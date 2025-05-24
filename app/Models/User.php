<?php

    namespace App\Models;

    use Exception;
    use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Facades\Gate;
    use Intervention\Image\Facades\Image;
    use Laravel\Sanctum\HasApiTokens;

    /**
     * @method static where(string $string, bool $true)
     * @method getProvider($provider)
     * @method providers()
     * @method truncate()
     * @method create(array $array)
     * @method static find($end_by)
     * @method static select(string $string)
     *
     * @property mixed       is_admin
     * @property mixed       first_name
     * @property mixed       last_name
     * @property mixed       id
     * @property mixed       roles
     * @property int|null    $two_factor_code
     * @property Carbon|null $two_factor_expires_at
     */
    class User extends Authenticatable implements MustVerifyEmail
    {
        use HasApiTokens, Notifiable;

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
        protected $fillable = [
            'first_name',
            'last_name',
            'api_token',
            'password',
            'image',
            'email',
            'status',
            'is_admin',
            'is_customer',
            'active_portal',
            'two_factor',
            'two_factor_code',
            'two_factor_expires_at',
            'locale',
            'sms_unit',
            'timezone',
            'provider',
            'provider_id',
            'email_verified_at',
            'two_factor_backup_code',
            'api_sending_server',
            'webhook_url',
            'dlt_entity_id',
            'dlt_telemarketer_id',
        ];

        /**
         * The attributes that should be hidden for arrays.
         *
         * @var array
         */
        protected $hidden = [
            'password',
            'remember_token',
        ];

        /**
         * The attributes that should be cast to native types.
         *
         * @var array
         */
        protected $casts = [
            'is_admin'              => 'boolean',
            'is_customer'           => 'boolean',
            'status'                => 'boolean',
            'two_factor'            => 'boolean',
            'last_access_at'        => 'datetime',
            'two_factor_expires_at' => 'datetime',
        ];

        /**
         * Find item by uid.
         */
        public static function findByUid($uid): object
        {
            return self::where('uid', $uid)->first();
        }

        public static function boot(): void
        {
            parent::boot();

            // Create uid when creating list.
            static::creating(function ($item) {
                // Create new uid
                $uid = uniqid();
                while (self::where('uid', $uid)->count() > 0) {
                    $uid = uniqid();
                }
                $item->uid = $uid;

                if (config('app.two_factor')) {
                    $item->two_factor_backup_code = self::generateTwoFactorBackUpCode();
                }

            });
        }

        /**
         * generate two factor backup code
         *
         * @return false|string
         */
        public static function generateTwoFactorBackUpCode(): bool|string
        {
            $backUpCode = [];
            for ($i = 0; $i < 8; $i++) {
                $backUpCode[] = rand(100000, 999999);
            }

            return json_encode($backUpCode);
        }

        public function customer(): HasOne
        {
            return $this->hasOne(Customer::class);
        }

        public function admin(): HasOne
        {
            return $this->hasOne(Admin::class);
        }

        /**
         * Check if user has admin account.
         */
        public function isAdmin(): bool
        {
            return $this->is_admin == 1;
        }

        /*
         *  Display Username
         */

        /**
         * Check if user has admin account.
         */
        public function isCustomer(): bool
        {
            return $this->is_customer == 1;
        }

        public function displayName(): string
        {
            return $this->first_name . ' ' . $this->last_name;
        }

        /**
         * generate two-factor code
         */
        public function generateTwoFactorCode(): void
        {
            $this->timestamps            = false;
            $this->two_factor_code       = rand(100000, 999999);
            $this->two_factor_expires_at = now()->addMinutes(10);
            $this->save();
        }

        /**
         * Reset two-factor code
         */
        public function resetTwoFactorCode(): void
        {
            $this->timestamps            = false;
            $this->two_factor_code       = null;
            $this->two_factor_expires_at = null;
            $this->save();
        }

        /**
         * Upload and resize avatar.
         */
        public function uploadImage($file): string
        {
            $path        = 'app/profile/';
            $upload_path = storage_path($path);

            if ( ! file_exists($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $filename = 'avatar-' . $this->id . '.' . $file->getClientOriginalExtension();

            // save to server
            $file->move($upload_path, $filename);

            // create thumbnails
            $img = Image::make($upload_path . $filename);

            $img->fit(120, 120, function ($c) {
                $c->aspectRatio();
                $c->upsize();
            })->save($upload_path . $filename . '.thumb.jpg');

            return $path . $filename;
        }

        /**
         * Get image thumb path.
         */
        public function imagePath(): string
        {
            if ( ! empty($this->image) && ! empty($this->id)) {
                return storage_path($this->image) . '.thumb.jpg';
            } else {
                return '';
            }
        }

        /**
         * Get image thumb path.
         */
        public function removeImage(): void
        {
            if ( ! empty($this->image) && ! empty($this->id)) {
                $path = storage_path($this->image);
                if (is_file($path)) {
                    unlink($path);
                }
                if (is_file($path . '.thumb.jpg')) {
                    unlink($path . '.thumb.jpg');
                }
            }
        }

        public function getCanEditAttribute(): bool
        {
            return auth()->id() === 1;
        }

        public function getCanDeleteAttribute(): bool
        {
            return $this->id !== auth()->id() && (Gate::check('delete customer'));
        }

        public function getIsSuperAdminAttribute(): bool
        {
            return $this->id === 1;
        }

        /**
         * Many-to-Many relations with Role.
         */
        public function roles(): BelongsToMany
        {
            return $this->belongsToMany(Role::class);
        }

        public function hasRole($name): bool
        {
            return $this->roles->contains('name', $name);
        }

        public function getPermissions(): Collection
        {
            $permissions = [];

            foreach ($this->roles as $role) {
                foreach ($role->permissions as $permission) {
                    if ( ! in_array($permission, $permissions, true)) {
                        $permissions[] = $permission;
                    }
                }
            }

            return collect($permissions);
        }

        /**
         * @return true
         */
        public function countSMSUnit($sms_unit): bool
        {
            if ($this->sms_unit != '-1') {
                $this->decrement('sms_unit', $sms_unit);
            }

            return true;
        }

        public function smsUnit()
        {
            return $this->sms_unit;
        }

        // Get the current time in Customer timezone

        /**
         * @throws Exception
         */
        public function getCurrentTime()
        {
            return $this->parseDateTime(null);
        }

        /**
         * @throws Exception
         */
        public function parseDateTime($datetime, $fallback = false)
        {
            // IMPORTANT: datetime string must NOT contain timezone information
            try {
                $dt = \Carbon\Carbon::parse($datetime, $this->timezone);
                $dt = $dt->timezone($this->timezone);
            } catch (Exception $ex) {
                if ($fallback) {
                    $dt = $this->parseDateTime('1900-01-01');
                } else {
                    throw $ex;
                }
            }

            return $dt;
        }

        public function sendingServers()
        {
            return $this->hasMany(CustomerBasedSendingServer::class);
        }

        /**
         * get route key by uid
         */
        public function getRouteKeyName(): string
        {
            return 'uid';
        }

        public function announcements()
        {
            return $this->belongsToMany(Announcements::class)
                ->withPivot('read_at')
                ->withTimestamps();
        }

    }
