<?php

    namespace App\Http\Controllers\Admin;

    use App\Exceptions\GeneralException;
    use App\Http\Requests\SenderID\StoreSenderidPlan;
    use App\Http\Requests\SenderID\StoreSenderidRequest;
    use App\Http\Requests\SenderID\UpdateSenderidRequest;
    use App\Http\Requests\SenderID\UpdateSenderidStatusRequest;
    use App\Library\Tool;
    use App\Models\Currency;
    use App\Models\Senderid;
    use App\Models\SenderidPlan;
    use App\Models\User;
    use App\Repositories\Contracts\SenderIDRepository;
    use Exception;
    use Generator;
    use Illuminate\Auth\Access\AuthorizationException;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\View\Factory;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\RedirectResponse;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\View\View;
    use JetBrains\PhpStorm\NoReturn;
    use OpenSpout\Common\Exception\InvalidArgumentException;
    use OpenSpout\Common\Exception\IOException;
    use OpenSpout\Common\Exception\UnsupportedTypeException;
    use OpenSpout\Writer\Exception\WriterNotOpenedException;
    use Rap2hpoutre\FastExcel\FastExcel;
    use Symfony\Component\HttpFoundation\BinaryFileResponse;

    class SenderIDController extends AdminBaseController
    {

        protected SenderIDRepository $sender_ids;


        /**
         * SenderIDController constructor.
         *
         * @param SenderIDRepository $sender_ids
         */

        public function __construct(SenderIDRepository $sender_ids)
        {
            $this->sender_ids = $sender_ids;
        }

        /**
         * @return Application|Factory|View
         * @throws AuthorizationException
         */

        public function index(): Factory|View|Application
        {

            $this->authorize('view sender_id');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Sending')],
                ['name' => __('locale.menu.Sender ID')],
            ];

            return view('admin.SenderID.index', compact('breadcrumbs'));
        }


        /**
         * @param Request $request
         *
         * @return void
         * @throws AuthorizationException
         */
        #[NoReturn] public function search(Request $request): void
        {

            $this->authorize('view sender_id');

            $columns = [
                0 => 'responsive_id',
                1 => 'uid',
                2 => 'uid',
                3 => 'sender_id',
                4 => 'user_id',
                5 => 'price',
                6 => 'status',
                7 => 'action',
            ];

            $totalData = Senderid::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            if (empty($request->input('search.value'))) {
                $sender_ids = Senderid::offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $search = $request->input('search.value');

                $sender_ids = Senderid::whereLike(['uid', 'sender_id', 'price', 'status', 'user.first_name', 'user.last_name'], $search)
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = Senderid::whereLike(['uid', 'sender_id', 'price', 'status', 'user.first_name', 'user.last_name'], $search)->count();

            }

            $data = [];
            if ( ! empty($sender_ids)) {
                foreach ($sender_ids as $senderid) {
                    $show = route('admin.senderid.show', $senderid->uid);

                    if ($senderid->user->is_admin) {
                        $assign_to = $senderid->user->displayName();
                    } else {

                        $customer_profile = route('admin.customers.show', $senderid->user->uid);
                        $customer_name    = $senderid->user->displayName();

                        $assign_to = "<a href='$customer_profile' class='text-primary mr-1'>$customer_name</a>";
                    }

                    if ($senderid->status == Senderid::STATUS_ACTIVE) {
                        $status = '<span class="badge bg-success text-uppercase">' . __('locale.labels.active') . '</span>';
                    } else if ($senderid->status == Senderid::STATUS_PENDING) {
                        $status = '<span class="badge bg-primary text-uppercase">' . __('locale.labels.pending') . '</span>';
                    } else if ($senderid->status == Senderid::STATUS_PAYMENT_REQUIRED) {
                        $status = '<span class="badge bg-info text-uppercase">' . __('locale.labels.payment_required') . '</span>';
                    } else if ($senderid->status == Senderid::STATUS_EXPIRED) {
                        $status = '<span class="badge bg-warning text-uppercase">' . __('locale.labels.expired') . '</span>';
                    } else if ($senderid->status == Senderid::STATUS_INACTIVE) {
                        $status = '<span class="badge bg-secondary text-uppercase">' . __('locale.labels.inactive') . '</span>';
                    } else {
                        $status = '<span class="badge bg-danger text-uppercase">' . __('locale.labels.blocked') . '</span>';
                    }

                    $status_dropdown_list = (in_array($senderid->status, [Senderid::STATUS_ACTIVE, Senderid::STATUS_BLOCKED])) ?
                        [Senderid::STATUS_PENDING, Senderid::STATUS_INACTIVE, Senderid::STATUS_PAYMENT_REQUIRED, Senderid::STATUS_EXPIRED] :
                        [Senderid::STATUS_PENDING, Senderid::STATUS_INACTIVE, Senderid::STATUS_BLOCKED, Senderid::STATUS_PAYMENT_REQUIRED, Senderid::STATUS_EXPIRED];

                    $nestedData['responsive_id']        = '';
                    $nestedData['avatar']               = route('admin.customers.avatar', $senderid->user->uid);
                    $nestedData['email']                = $senderid->user->email;
                    $nestedData['uid']                  = $senderid->uid;
                    $nestedData['sender_id']            = $senderid->sender_id;
                    $nestedData['user_id']              = $assign_to;
                    $nestedData['created_at']           = Tool::formatDateTime($senderid->created_at);
                    $nestedData['price']                = "<div>
                                                        <p class='text-bold-600'>" . Tool::format_price($senderid->price, $senderid->currency->format) . " </p>
                                                        <p class='text-muted'>" . $senderid->displayFrequencyTime() . "</p>
                                                   </div>";
                    $nestedData['status']               = $status;
                    $nestedData['actual_status']        = $senderid->status;
                    $nestedData['can_activate']         = in_array($senderid->status, [Senderid::STATUS_BLOCKED, Senderid::STATUS_EXPIRED, Senderid::STATUS_PENDING, Senderid::STATUS_PAYMENT_REQUIRED, Senderid::STATUS_INACTIVE]);
                    $nestedData['can_block']            = in_array($senderid->status, [Senderid::STATUS_ACTIVE, Senderid::STATUS_EXPIRED, Senderid::STATUS_PENDING, Senderid::STATUS_PAYMENT_REQUIRED, Senderid::STATUS_INACTIVE]);
                    $nestedData['status_dropdown_list'] = $status_dropdown_list;
                    $nestedData['edit']                 = $show;
                    $data[]                             = $nestedData;

                }
            }

            $json_data = [
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => $totalData,
                "recordsFiltered" => $totalFiltered,
                "data"            => $data,
            ];

            echo json_encode($json_data);
            exit();

        }

        /**
         * @return Application|Factory|View
         * @throws AuthorizationException
         */

        public function create(): Factory|View|Application
        {
            $this->authorize('create sender_id');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/senderid"), 'name' => __('locale.menu.Sender ID')],
                ['name' => __('locale.sender_id.add_new_sender_id')],
            ];

            $customers  = User::where('status', true)->get();
            $currencies = Currency::where('status', true)->get();

            return view('admin.SenderID.create', compact('breadcrumbs', 'currencies', 'customers'));
        }


        /**
         * View sender id for edit
         *
         * @param Senderid $senderid
         *
         * @return Application|Factory|View
         *
         * @throws AuthorizationException
         */

        public function show(Senderid $senderid): Factory|View|Application
        {
            $this->authorize('edit sender_id');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/senderid"), 'name' => __('locale.menu.Sender ID')],
                ['name' => __('locale.sender_id.update_sender_id')],
            ];


            $customers  = User::where('status', true)->get();
            $currencies = Currency::where('status', true)->get();


            return view('admin.SenderID.show', compact('breadcrumbs', 'senderid', 'customers', 'currencies'));
        }


        /**
         * @param StoreSenderidRequest $request
         * @param Senderid             $senderid
         *
         * @return RedirectResponse
         */

        public function store(StoreSenderidRequest $request, Senderid $senderid): RedirectResponse
        {
            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.senderid.index')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            if (config('app.trai_dlt') && $request->input('is_dlt') == 'on') {
                $validator = Validator::make($request->all(), [
                    'entity_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return redirect()->route('admin.senderid.create')->withInput($request->all())->withErrors($validator->errors());
                }
            }

            $this->sender_ids->store($request->input(), $senderid::billingCycleValues());

            return redirect()->route('admin.senderid.index')->with([
                'status'  => 'success',
                'message' => __('locale.sender_id.sender_id_successfully_added'),
            ]);

        }


        /**
         * @param Senderid              $senderid
         * @param UpdateSenderidRequest $request
         *
         * @return RedirectResponse
         */

        public function update(Senderid $senderid, UpdateSenderidRequest $request): RedirectResponse
        {
            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.senderid.index')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $this->sender_ids->update($senderid, $request->input(), $senderid::billingCycleValues());

            return redirect()->route('admin.senderid.index')->with([
                'status'  => 'success',
                'message' => __('locale.sender_id.sender_id_successfully_updated'),
            ]);
        }

        /**
         * @param Senderid $senderid
         *
         * @return JsonResponse
         *
         * @throws AuthorizationException
         */
        public function destroy(Senderid $senderid): JsonResponse
        {
            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            $this->authorize('delete sender_id');

            $this->sender_ids->destroy($senderid);

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.sender_id.sender_id_successfully_deleted'),
            ]);

        }

        /**
         * Bulk Action with Enable, Disable and Delete
         *
         * @param Request $request
         *
         * @return JsonResponse
         * @throws AuthorizationException
         */

        public function batchAction(Request $request): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $action = $request->get('action');
            $ids    = $request->get('ids');

            switch ($action) {
                case 'destroy':
                    $this->authorize('delete sender_id');

                    $this->sender_ids->batchDestroy($ids);

                    return response()->json([
                        'status'  => 'success',
                        'message' => __('locale.sender_id.senderids_deleted'),
                    ]);

                case 'active':
                    $this->authorize('edit sender_id');

                    $this->sender_ids->batchActive($ids);

                    return response()->json([
                        'status'  => 'success',
                        'message' => __('locale.sender_id.senderids_active'),
                    ]);

                case 'block':

                    $this->authorize('edit sender_id');

                    $this->sender_ids->batchBlock($ids);

                    return response()->json([
                        'status'  => 'success',
                        'message' => __('locale.sender_id.senderids_block'),
                    ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => __('locale.exceptions.invalid_action'),
            ]);

        }


        /**
         * @return Generator
         */

        public function senderidGenerator(): Generator
        {
            foreach (Senderid::cursor() as $senderid) {
                yield $senderid;
            }
        }

        /**
         * @return RedirectResponse|BinaryFileResponse
         * @throws AuthorizationException
         */
        public function export(): BinaryFileResponse|RedirectResponse
        {
            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.senderid.index')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $this->authorize('view sender_id');

            try {
                $file_name = (new FastExcel($this->senderidGenerator()))->export(storage_path('Senderid_' . time() . '.xlsx'));

                return response()->download($file_name);

            } catch (IOException|InvalidArgumentException|UnsupportedTypeException|WriterNotOpenedException $e) {
                return redirect()->route('admin.senderid.index')->with([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ]);
            }

        }


        /**
         * @return Application|Factory|View
         * @throws AuthorizationException
         */

        public function plan(): Factory|View|Application
        {

            $this->authorize('view sender_id');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/senderid"), 'name' => __('locale.menu.Sender ID')],
                ['name' => __('locale.menu.Plan')],
            ];

            return view('admin.SenderID.plan', compact('breadcrumbs'));
        }


        /**
         * @param Request $request
         *
         * @return void
         * @throws AuthorizationException
         */
        #[NoReturn] public function searchPlan(Request $request): void
        {

            $this->authorize('view sender_id');

            $columns = [
                0 => 'responsive_id',
                1 => 'uid',
                2 => 'uid',
                3 => 'price',
                4 => 'renew',
                5 => 'action',
            ];

            $totalData = SenderidPlan::count();

            $totalFiltered = $totalData;

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir   = $request->input('order.0.dir');

            if (empty($request->input('search.value'))) {
                $sender_ids_plan = SenderidPlan::offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
            } else {
                $search = $request->input('search.value');

                $sender_ids_plan = SenderidPlan::whereLike(['uid', 'price'], $search)
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();

                $totalFiltered = SenderidPlan::whereLike(['uid', 'price'], $search)->count();

            }

            $data = [];
            if ( ! empty($sender_ids_plan)) {
                foreach ($sender_ids_plan as $plan) {

                    $nestedData['responsive_id'] = '';
                    $nestedData['uid']           = $plan->uid;
                    $nestedData['price']         = Tool::format_price($plan->price, $plan->currency->format);
                    $nestedData['renew']         = __('locale.labels.every') . ' ' . $plan->displayFrequencyTime();
                    $data[]                      = $nestedData;

                }
            }

            $json_data = [
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => $totalData,
                "recordsFiltered" => $totalFiltered,
                "data"            => $data,
            ];

            echo json_encode($json_data);
            exit();

        }


        /**
         * @return Application|Factory|View
         * @throws AuthorizationException
         */

        public function createPlan(): Factory|View|Application
        {
            $this->authorize('create sender_id');

            $breadcrumbs = [
                ['link' => url(config('app.admin_path') . "/dashboard"), 'name' => __('locale.menu.Dashboard')],
                ['link' => url(config('app.admin_path') . "/senderid"), 'name' => __('locale.menu.Sender ID')],
                ['link' => url(config('app.admin_path') . "/senderid/plan"), 'name' => __('locale.menu.Plan')],
                ['name' => __('locale.labels.create_plan')],
            ];

            $currencies = Currency::where('status', true)->get();

            return view('admin.SenderID.create-plan', compact('breadcrumbs', 'currencies'));
        }


        /**
         * @param StoreSenderidPlan $request
         * @param Senderid          $senderid
         *
         * @return RedirectResponse
         */

        public function storePlan(StoreSenderidPlan $request, Senderid $senderid): RedirectResponse
        {

            if (config('app.stage') == 'demo') {
                return redirect()->route('admin.senderid.plan')->with([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $this->sender_ids->storePlan($request->except('_token'), $senderid::billingCycleValues());

            return redirect()->route('admin.senderid.plan')->with([
                'status'  => 'success',
                'message' => __('locale.plans.plan_successfully_added'),
            ]);

        }

        /**
         * @param SenderidPlan $plan
         *
         * @return JsonResponse
         * @throws GeneralException
         * @throws Exception
         */
        public function deletePlan(SenderidPlan $plan): JsonResponse
        {

            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            if ( ! $plan->delete()) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.plans.plan_successfully_deleted'),
            ]);
        }

        /**
         * delete batch sender id plans
         *
         * @param Request $request
         *
         * @return JsonResponse
         * @throws GeneralException
         */
        public function deleteBatchPlan(Request $request): JsonResponse
        {
            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }


            $ids    = $request->get('ids');
            $status = SenderidPlan::whereIn('uid', $ids)->delete();

            if ( ! $status) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.plans.plan_successfully_deleted'),
            ]);

        }

        /*Version 3.11*/


        /**
         * @param Senderid                    $senderid
         * @param UpdateSenderidStatusRequest $request
         *
         * @return JsonResponse
         */

        public function updateStatus(Senderid $senderid, UpdateSenderidStatusRequest $request): JsonResponse
        {
            if (config('app.stage') == 'demo') {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! This option is not available in demo mode',
                ]);
            }

            $this->sender_ids->update($senderid, $request->except('_token'), $senderid::billingCycleValues());

            return response()->json([
                'status'  => 'success',
                'message' => __('locale.sender_id.sender_id_successfully_updated'),
            ]);
        }

    }
