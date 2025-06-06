@extends('layouts/contentLayoutMaster')

@section('title', __('locale.plans.add_new_plan'))

@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('content')
    <!-- Basic Vertical form layout section start -->
    <section id="basic-vertical-layouts">
        <div class="row match-height">
            <div class="col-md-6 col-12">

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title"> {{__('locale.plans.add_new_plan')}} </h4>
                    </div>
                    <div class="card-content">
                        <div class="card-body">
                            <p>{!! __('locale.description.plan_details') !!} </p>
                            <div class="form-body">
                                <form class="form form-vertical" action="{{ route('admin.plans.store') }}"
                                      method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="name"
                                                       class="form-label required">{{__('locale.labels.name')}}</label>
                                                <input type="text" id="name"
                                                       class="form-control @error('name') is-invalid @enderror"
                                                       value="{{ old('name') }}" name="name" required>
                                                @error('name')
                                                <p><small class="text-danger">{{ $message }}</small></p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="price"
                                                       class="form-label required">{{__('locale.plans.price')}}</label>
                                                <input type="text" id="price"
                                                       class="form-control text-right @error('price') is-invalid @enderror"
                                                       value="{{ old('price') }}" name="price" required>
                                                @error('price')
                                                <p><small class="text-danger">{{ $message }}</small></p>
                                                @enderror
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="billing_cycle"
                                                       class="form-label required">{{__('locale.plans.billing_cycle')}}</label>
                                                <select class="form-select" id="billing_cycle" name="billing_cycle">
                                                    @foreach (\App\Enums\BillingCycleEnum::getAllValues() as $cycle)
                                                        <option value="{{ $cycle }}" {{ old('billing_cycle') || 'monthly' == $cycle ? 'selected': null }}> {{__('locale.labels.'.$cycle)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('billing_cycle')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>


                                        <div class="col-sm-6 col-12 show-custom">
                                            <div class="mb-1">
                                                <label for="frequency_amount"
                                                       class="form-label required">{{__('locale.plans.frequency_amount')}}</label>
                                                <input type="text" id="frequency_amount"
                                                       class="form-control text-right @error('frequency_amount') is-invalid @enderror"
                                                       value="{{ old('frequency_amount') }}" name="frequency_amount">
                                                @error('frequency_amount')
                                                <p><small class="text-danger">{{ $message }}</small></p>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-sm-6 col-12 show-custom">
                                            <div class="mb-1">
                                                <label for="frequency_unit"
                                                       class="form-label required">{{__('locale.plans.frequency_unit')}}</label>
                                                <select class="form-select" id="frequency_unit" name="frequency_unit">
                                                    <option value="day"> {{__('locale.labels.day')}}</option>
                                                    <option value="week">  {{__('locale.labels.week')}}</option>
                                                    <option value="month">  {{__('locale.labels.month')}}</option>
                                                    <option value="year">  {{__('locale.labels.year')}}</option>
                                                </select>
                                            </div>
                                            @error('frequency_unit')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>


                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="currency_id"
                                                       class="form-label required">{{__('locale.labels.currency')}}</label>
                                                <select class="form-select select2" id="currency_id" name="currency_id">
                                                    @foreach($currencies as $currency)
                                                        <option value="{{$currency->id}}"> {{ $currency->name }}
                                                            ({{$currency->code}})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('currency_id')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>


                                        <div class="col-12">
                                            <div class="mb-1">
                                                <div class="form-check me-3 me-lg-5 mt-1">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="show_in_customer" checked value="true"
                                                           name="show_in_customer">
                                                    <label for="show_in_customer"
                                                           class="form-label">{{__('locale.plans.show_in_customer')}}</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-1">
                                                <div class="form-check me-3 me-lg-5 mt-1">
                                                    <input type="checkbox" class="form-check-input"
                                                           id="tax_billing_required" value="true"
                                                           name="tax_billing_required">
                                                    <label for="tax_billing_required"
                                                           class="form-label">{{__('locale.plans.billing_information_required')}}</label>
                                                </div>
                                                <p>
                                                    <small class="text-muted">{{__('locale.plans.ask_tax_billing_information_subscribing_plan')}}</small>
                                                </p>

                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-1">
                                                <div class="form-check me-3 me-lg-5 mt-1">
                                                    <input type="checkbox" class="form-check-input" id="is_popular"
                                                           value="true" name="is_popular">
                                                    <label class="form-label"
                                                           for="is_popular">{{__('locale.labels.is_popular')}}</label>
                                                </div>
                                                <p>
                                                    <small class="text-muted">{{__('locale.plans.set_this_plan_as_popular')}}</small>
                                                </p>
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <div class="mb-1">
                                                <div class="form-check me-3 me-lg-5 mt-1">
                                                    <input type="checkbox" class="form-check-input" id="is_dlt"
                                                           value="true" name="is_dlt">
                                                    <label class="form-label"
                                                           for="dlt">{{__('locale.labels.dlt')}}</label>
                                                </div>
                                                <p><small class="text-muted">{{__('locale.labels.trai_dlt')}}</small>
                                                </p>
                                            </div>
                                        </div>


                                        <div class="col-12 mt-2">
                                            <button type="submit" class="btn btn-primary mb-1">
                                                <i data-feather="save"></i> {{__('locale.buttons.save')}}
                                            </button>
                                        </div>


                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>


            </div>
        </div>
    </section>
    <!-- // Basic Vertical form layout section end -->

@endsection

@section('vendor-script')
    <!-- vendor files -->
    <script src="{{ asset(mix('vendors/js/forms/select/select2.full.min.js')) }}"></script>
@endsection


@section('page-script')

    <script>
        let showCustom = $('.show-custom');
        let billing_cycle = $('#billing_cycle');

        let firstInvalid = $('form').find('.is-invalid').eq(0);

        if (firstInvalid.length) {
            $('body, html').stop(true, true).animate({
                'scrollTop': firstInvalid.offset().top - 200 + 'px'
            }, 200);
        }

        if (billing_cycle.val() === 'custom') {
            showCustom.show();
        } else {
            showCustom.hide();
        }

        billing_cycle.on('change', function () {
            if (billing_cycle.val() === 'custom') {
                showCustom.show();
            } else {
                showCustom.hide();
            }

        });

        // Basic Select2 select
        $(".select2").each(function () {
            let $this = $(this);
            $this.wrap('<div class="position-relative"></div>');
            $this.select2({
                // the following code is used to disable x-scrollbar when click in select input and
                // take 100% width in responsive also
                dropdownAutoWidth: true,
                width: '100%',
                dropdownParent: $this.parent()
            });
        });

    </script>
@endsection
