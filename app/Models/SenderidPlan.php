<?php

namespace App\Models;

use App\Library\Tool;
use App\Library\Traits\HasUid;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, string $uid)
 * @method static count()
 * @method static offset(mixed $start)
 * @method static whereLike(string[] $array, mixed $search)
 * @method static create(array $input)
 * @method static whereIn(string $string, mixed $ids)
 * @method static cursor()
 * @method static find(mixed $plan)
 */
class SenderidPlan extends Model
{

    use HasUid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
            'price',
            'billing_cycle',
            'frequency_amount',
            'frequency_unit',
            'currency_id',
    ];

    /**
     * Currency
     *
     * @return BelongsTo
     *
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }


    /**
     * Get billing recurs available values.
     *
     * @return array
     */
    public static function billingCycleValues(): array
    {
        return [
                'daily'   => [
                        'frequency_amount' => 1,
                        'frequency_unit'   => 'day',
                ],
                'monthly' => [
                        'frequency_amount' => 1,
                        'frequency_unit'   => 'month',
                ],
                'yearly'  => [
                        'frequency_amount' => 1,
                        'frequency_unit'   => 'year',
                ],
        ];
    }

    /**
     * Frequency time unit options.
     *
     * @return array
     */
    public static function timeUnitOptions(): array
    {
        return [
                ['value' => 'day', 'text' => 'day'],
                ['value' => 'week', 'text' => 'week'],
                ['value' => 'month', 'text' => 'month'],
                ['value' => 'year', 'text' => 'year'],
        ];
    }

    /**
     * Check if phone number validity time is unlimited.
     *
     * @return bool
     */
    public function isTimeUnlimited(): bool
    {
        return $this->frequency_unit == 'unlimited';
    }


    /**
     * Display frequency time
     *
     * @return array|Application|Translator|string|null
     */
    public function displayFrequencyTime()
    {
        // unlimited
        if ($this->isTimeUnlimited()) {
            return __('locale.labels.unlimited');
        }

        return $this->frequency_amount . ' ' . Tool::getPluralParse(__('locale.labels.' . $this->frequency_unit), $this->frequency_amount);
    }
}
