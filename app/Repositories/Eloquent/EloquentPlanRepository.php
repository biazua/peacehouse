<?php

    namespace App\Repositories\Eloquent;

    use App\Exceptions\GeneralException;
    use App\Models\Plan;
    use App\Models\PlansCoverageCountries;
    use App\Models\PlanSendingCreditPrice;
    use App\Models\Subscription;
    use App\Repositories\Contracts\PlanRepository;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\DB;
    use Throwable;

    class EloquentPlanRepository extends EloquentBaseRepository implements PlanRepository
    {

        /**
         * EloquentPlanRepository constructor.
         *
         * @param Plan $plan
         */
        public function __construct(Plan $plan)
        {
            parent::__construct($plan);
        }

        /**
         * @param array $input
         * @param array $options
         * @param array $billingCycle
         *
         * @return Plan
         * @throws GeneralException
         */

        public function store(array $input, array $options, array $billingCycle): Plan
        {

            /** @var Plan $plan */
            $plan = $this->make(Arr::only($input, [
                'name',
                'price',
                'billing_cycle',
                'frequency_amount',
                'frequency_unit',
                'currency_id',
                'is_popular',
                'tax_billing_required',
                'show_in_customer',
                'is_dlt',
            ]));


            if (isset($input['tax_billing_required'])) {
                $plan->tax_billing_required = true;
            }

            if (isset($input['is_popular'])) {
                $plan->is_popular = true;
            }

            if (isset($input['show_in_customer'])) {
                $plan->show_in_customer = true;
            } else {
                $plan->show_in_customer = false;
            }

            if (isset($input['billing_cycle']) && $input['billing_cycle'] != 'custom') {
                $limits                 = $billingCycle[$input['billing_cycle']];
                $plan->frequency_amount = $limits['frequency_amount'];
                $plan->frequency_unit   = $limits['frequency_unit'];
            }

            if (isset($input['is_dlt'])) {
                $plan->is_dlt = true;
            }

            $plan->options = json_encode($options);

            $plan->status  = false;
            $plan->user_id = auth()->user()->id;

            if ( ! $this->save($plan)) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return $plan;

        }

        /**
         * @param Plan $plan
         *
         * @return bool
         */
        private function save(Plan $plan): bool
        {
            if ( ! $plan->save()) {
                return false;
            }

            return true;
        }

        /**
         * @param Plan  $plan
         * @param array $input
         *
         * @param array $billingCycle
         *
         * @return Plan
         * @throws GeneralException
         */
        public function update(Plan $plan, array $input, array $billingCycle): Plan
        {
            if (isset($input['tax_billing_required'])) {
                $input['tax_billing_required'] = true;
            } else {
                $input['tax_billing_required'] = false;
            }

            if (isset($input['is_popular'])) {
                $input['is_popular'] = true;
            } else {
                $input['is_popular'] = false;
            }

            if (isset($input['show_in_customer'])) {
                $input['show_in_customer'] = true;
            } else {
                $input['show_in_customer'] = false;
            }

            if (isset($input['is_dlt'])) {
                $input['is_dlt'] = true;
            } else {
                $input['is_dlt'] = false;
            }

            if (isset($input['billing_cycle']) && $input['billing_cycle'] != 'custom') {
                $limits                    = $billingCycle[$input['billing_cycle']];
                $input['frequency_amount'] = $limits['frequency_amount'];
                $input['frequency_unit']   = $limits['frequency_unit'];
            }

            if ( ! $plan->update($input)) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return $plan;
        }

        /**
         * delete plan
         *
         * @param Plan $plan
         *
         * @return bool
         * @throws GeneralException
         */
        public function destroy(Plan $plan): bool
        {

            Subscription::where('plan_id', $plan->id)->delete();

            if ( ! $plan->delete()) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return true;
        }

        /**
         * @param array $ids
         *
         * @return mixed
         * @throws Exception|Throwable
         *
         */
        public function batchDestroy(array $ids): bool
        {

            $plans = Plan::whereIn('uid', $ids)->cursor();
            foreach ($plans as $plan) {
                Subscription::where('plan_id', $plan->id)->delete();
                $plan->delete();
            }

            return true;
        }

        /**
         * @param array $ids
         *
         * @return mixed
         * @throws Exception|Throwable
         *
         */
        public function batchActive(array $ids): bool
        {
            DB::transaction(function () use ($ids) {
                if ($this->query()->whereIn('uid', $ids)
                    ->update(['status' => true])
                ) {
                    return true;
                }

                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            });

            return true;
        }

        /**
         * @param array $ids
         *
         * @return mixed
         * @throws Exception|Throwable
         *
         */
        public function batchDisable(array $ids): bool
        {
            DB::transaction(function () use ($ids) {
                if ($this->query()->whereIn('uid', $ids)
                    ->update(['status' => false])
                ) {
                    return true;
                }

                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            });

            return true;
        }


        /**
         * update speed limit
         *
         * @param Plan  $plan
         * @param array $input
         *
         * @return bool
         * @throws GeneralException
         */

        public function updateSpeedLimits(Plan $plan, array $input): bool
        {
            $get_options  = json_decode($plan->options, true);
            $output       = array_replace($get_options, $input);
            $sendingLimit = $input['sending_limit'];

            if ($sendingLimit != 'custom' && $sendingLimit != 'other') {
                $output['sending_quota']           = $output['quota_value'];
                $output['sending_quota_time']      = $output['quota_base'];
                $output['sending_quota_time_unit'] = $output['quota_unit'];
            }

            if ( ! $plan->update(['options' => $output])) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return true;

        }


        /**
         * update sms pricing
         *
         * @param Plan  $plan
         * @param array $input
         *
         * @return bool
         * @throws GeneralException
         */

        public function updatePricing(Plan $plan, array $input): bool
        {
            $get_options = json_decode($plan->options, true);
            $output      = array_replace($get_options, $input);

            if ( ! $plan->update(['options' => $output])) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return true;

        }


        /**
         * copy existing plan
         *
         * @param Plan  $plan
         * @param array $input
         *
         * @return Plan
         * @throws GeneralException
         */

        public function copy(Plan $plan, array $input): Plan
        {

            if ( ! $new_plan = $plan->replicate()) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            $new_plan->name         = $input['plan_name'];
            $new_plan->custom_order = 0;
            $new_plan->status       = $plan->status;
            $new_plan->created_at   = Carbon::now();
            $new_plan->updated_at   = Carbon::now();

            if ( ! $new_plan->save()) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            /*Clone Coverage*/
            $plan_coverage = PlansCoverageCountries::where('plan_id', $plan->id)->get();
            if ($plan_coverage->count() > 0) {
                foreach ($plan_coverage as $coverage) {
                    $replicateCoverage          = $coverage->replicate();
                    $replicateCoverage->plan_id = $new_plan->id;
                    $replicateCoverage->save();
                }
            }

            return $new_plan;
        }


        /**
         *  Update Plan Sender ID for as a default Sender id for customer
         *
         * @param Plan  $plan
         * @param array $post_data
         *
         * @return Plan
         * @throws GeneralException
         */
        public function updateSenderID(Plan $plan, array $post_data): Plan
        {

            if (isset($post_data['sender_id_billing_cycle']) && $post_data['sender_id_billing_cycle'] != 'custom') {
                $limits                                  = $plan::billingCycleValues()[$post_data['sender_id_billing_cycle']];
                $post_data['sender_id_frequency_amount'] = $limits['frequency_amount'];
                $post_data['sender_id_frequency_unit']   = $limits['frequency_unit'];
            }

            $get_options = json_decode($plan->options, true);
            $output      = array_replace($get_options, $post_data);

            if ( ! $plan->update(['options' => json_encode($output)])) {
                throw new GeneralException(__('locale.exceptions.something_went_wrong'));
            }

            return $plan;

        }

        /**
         * Updates the credit price for a plan.
         *
         * @param Plan  $plan The plan for which to update the credit price.
         * @param array $post_data The array of credit price data to update.
         * @return bool Returns true if the update is successful.
         */

        /**
         * Updates the credit price for a plan.
         *
         * @param Plan  $plan The plan for which to update the credit price.
         * @param array $post_data The array of credit price data to update.
         * @return bool Returns true if the update is successful.
         */
        public function updateCreditPrice(Plan $plan, array $post_data)
        {
            $saved_ids = collect();

            foreach ($post_data as $data) {
                $creditPrice = PlanSendingCreditPrice::firstOrNew(['plan_id' => $plan->id, 'uid' => $data['uid']]);
                $creditPrice->fill(array_merge($data, ['plan_id' => $plan->id]))->save();

                // store saved ids
                $saved_ids->push($creditPrice->uid);
            }

            // Delete fields
            $plan->getCreditPrices()->whereNotIn('uid', $saved_ids->toArray())->delete();

            return true;
        }

        /**
         * @return true
         * @throws Throwable
         */
        public function batchCoverageEnable(Plan $plan, array $ids)
        {
            $batchEnable = PlansCoverageCountries::where('plan_id', $plan->id)->whereIn('uid', $ids)->update([
                'status' => true,
            ]);

            if ($batchEnable) {
                return true;
            }

            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }

        /**
         * @return true
         * @throws GeneralException
         */
        public function batchCoverageDisable(Plan $plan, array $ids)
        {

            $batchDisable = PlansCoverageCountries::where('plan_id', $plan->id)->whereIn('uid', $ids)->update([
                'status' => false,
            ]);

            if ($batchDisable) {
                return true;
            }

            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }

        /**
         * @return true
         * @throws GeneralException
         */
        public function batchCoverageDelete(Plan $plan, array $ids)
        {
            $batchDelete = PlansCoverageCountries::where('plan_id', $plan->id)->whereIn('uid', $ids)->delete();

            if ($batchDelete) {
                return true;
            }

            throw new GeneralException(__('locale.exceptions.something_went_wrong'));
        }

    }
