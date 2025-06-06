@php
    use App\Helpers\Helper;$configData = Helper::applClasses();
@endphp
@extends('layouts/fullLayoutMaster')

@section('title', __('locale.labels.unsubscribe'))


@section('page-style')
    <link rel="stylesheet" href="{{ asset(mix('css/base/pages/authentication.css')) }}">

    @if(config('no-captcha.registration'))
        {!! RecaptchaV3::initJs() !!}
    @endif

@endsection

@section('content')
    <div class="auth-wrapper auth-cover">
        <div class="auth-inner row m-0">
            <!-- Brand logo-->
            <a class="brand-logo" href="{{route('login')}}">
                <img src="{{asset(config('app.logo'))}}" alt="{{config('app.name')}}"/>
            </a>
            <!-- /Brand logo-->

            <!-- Left Text-->
            <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
                    @if($configData['theme'] === 'dark')
                        <img src="{{asset('images/pages/reset-password-v2-dark.svg')}}" class="img-fluid"
                             alt="{{ config('app.name') }}"/>
                    @else
                        <img src="{{asset('images/pages/reset-password-v2.svg')}}" class="img-fluid"
                             alt="{{ config('app.name') }}"/>
                    @endif
                </div>
            </div>
            <!-- /Left Text-->

            <!-- Reset password-->
            <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                    <h2 class="card-title fw-bold mb-1 text-danger">{{ __('locale.labels.unsubscribe') }}</h2>
                    <p class="card-text mb-2 text-danger">{{ __('locale.labels.unsubscribe') }} {{ __('locale.labels.from') }} {{ $contact->name }}</p>
                    <form method="POST" class="auth-reset-password-form mt-2"
                          action="{{ route('contacts.unsubscribe_url', $contact->uid) }}">
                        @csrf


                        @if(config('no-captcha.registration'))
                            @error('g-recaptcha-response')
                            <span class="text-danger">{{ __('locale.labels.g-recaptcha-response') }}</span>
                            @enderror
                        @endif


                        <div class="col-12">

                            <div class="mb-1">
                                <label for="phone" class="form-label required">{{ __('locale.labels.phone') }}</label>

                                <input type="number" id="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone')}}"
                                       name="phone"
                                       required>
                                @error('phone')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                                @enderror
                            </div>

                        </div>


                        @if(config('no-captcha.registration'))
                            <fieldset class="form-label-group position-relative">
                                {!! RecaptchaV3::field('unsubscribe') !!}
                            </fieldset>
                        @endif

                        <button class="btn btn-primary w-100" type="submit"
                                tabindex="3">{{ __('locale.labels.unsubscribe') }}</button>
                    </form>
                </div>
            </div>
            <!-- /Reset password-->
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let firstInvalid = $('form').find('.is-invalid').eq(0);

        if (firstInvalid.length) {
            $('body, html').stop(true, true).animate({
                'scrollTop': firstInvalid.offset().top - 200 + 'px'
            }, 200);
        }

    </script>
@endpush
