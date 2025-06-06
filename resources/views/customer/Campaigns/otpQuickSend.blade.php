@extends('layouts/contentLayoutMaster')

@section('title', __('locale.menu.Quick Send'))

@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('page-style')

    <style>
        .customized_select2 .select2-selection--single, .input_sender_id {
            border-left: 0;
            border-radius: 0 4px 4px 0;
            min-height: 2.75rem !important;
        }
    </style>

@endsection

@section('content')

    <!-- Basic Vertical form layout section start -->
    <section id="basic-vertical-layouts campaign_builder">

        <div class="row">
            <div class="col-md-8 col-12">
                <div class="alert alert-info" role="alert">
                    <div class="alert-body d-flex align-items-center">
                        <i data-feather="info" class="me-50"></i>
                        <span class="text-uppercase"> {{ __('locale.template_tags.not_work_with_quick_send')  }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row match-height">
            <div class="col-md-8 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">

                            <form class="form form-vertical" action="{{ route('customer.otp.quick_send') }}"
                                  method="post">
                                @csrf
                                <div class="row">

                                    @if($sendingServers->count() > 0)
                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="sending_server"
                                                       class="form-label required">{{ __('locale.labels.sending_server') }}</label>
                                                <select class="select2 form-select" name="sending_server">
                                                    @foreach($sendingServers as $server)
                                                        @if(isset($server->sendingServer) && $server->sendingServer->status == 1 && $server->sendingServer->otp)
                                                            <option value="{{$server->sendingServer->id}}"> {{ $server->sendingServer->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>

                                                @error('sending_server')
                                                <p><small class="text-danger">{{ $message }}</small></p>
                                                @enderror
                                            </div>
                                        </div>

                                    @endif

                                    @can('view_sender_id')
                                        @if(auth()->user()->customer->getOption('sender_id_verification') == 'yes')
                                            <div class="col-12">
                                                <p class="text-uppercase">{{ __('locale.labels.originator') }}</p>
                                            </div>

                                            <div class="col-md-6 col-12 customized_select2">
                                                <div class="mb-1">
                                                    <label for="sender_id"
                                                           class="form-label">{{ __('locale.labels.sender_id') }}
                                                        <a class="text-success text-decoration-underline mx-1 text-uppercase cursor-pointer text"
                                                           href="{{ route('customer.senderid.request') }}"
                                                           target="__blank">{{ __('locale.labels.request_new') }}</a>
                                                    </label>
                                                    <div class="input-group">
                                                        <div class="input-group-text">
                                                            <div class="form-check">
                                                                <input type="radio" class="form-check-input sender_id"
                                                                       name="originator" checked value="sender_id"
                                                                       id="sender_id_check" />
                                                                <label class="form-check-label"
                                                                       for="sender_id_check"></label>
                                                            </div>
                                                        </div>

                                                        <div style="width: 17rem">
                                                            <select class="form-select select2" id="sender_id"
                                                                    name="sender_id">
                                                                @foreach($sender_ids as $sender_id)
                                                                    <option value="{{$sender_id->sender_id}}"> {{ $sender_id->sender_id }} </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        @else
                                            @can('view_numbers')
                                                <div class="col-md-6 col-12 customized_select2">

                                                    <div class="mb-1">
                                                        <label for="sender_id"
                                                               class="form-label">{{ __('locale.labels.sender_id') }}
                                                            <span class="text-success font-small-1 text-uppercase text">
                                                                ({{ __('locale.labels.select_one_or_insert_your_own') }})
                                                            </span>

                                                        </label>
                                                        <div class="input-group">
                                                            <div class="input-group-text">
                                                                <div class="form-check">
                                                                    <input type="radio"
                                                                           class="form-check-input sender_id"
                                                                           name="originator" checked value="sender_id"
                                                                           id="sender_id_check" />
                                                                    <label class="form-check-label"
                                                                           for="sender_id_check"></label>
                                                                </div>
                                                            </div>

                                                            <div style="width: 17rem">
                                                                <select class="form-select select2"
                                                                        id="sender_id_custom"
                                                                        name="sender_id">
                                                                    @if(isset($sender_ids))
                                                                        @foreach($sender_ids as $sender_id)
                                                                            <option value="{{$sender_id->sender_id}}"> {{ $sender_id->sender_id }} </option>
                                                                        @endforeach
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            @else
                                                <div class="col-12">
                                                    <div class="mb-1">
                                                        <label for="sender_id"
                                                               class="form-label">{{__('locale.labels.sender_id')}}

                                                            <span class="text-success font-small-1 text-uppercase text">
                                                                ({{ __('locale.labels.select_one_or_insert_your_own') }})
                                                            </span>

                                                        </label>
                                                        <select class="form-select select2" id="sender_id_custom"
                                                                name="sender_id">
                                                            @if(isset($sender_ids))
                                                                @foreach($sender_ids as $sender_id)
                                                                    <option value="{{$sender_id->sender_id}}"> {{ $sender_id->sender_id }} </option>
                                                                @endforeach
                                                            @endif

                                                        </select>
                                                        @error('sender_id')
                                                        <p><small class="text-danger">{{ $message }}</small></p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @endcan
                                        @endif
                                    @endcan

                                    @can('view_numbers')
                                        <div class="col-md-6 col-12 customized_select2">
                                            <div class="mb-1">
                                                <label for="phone_number"
                                                       class="form-label">{{ __('locale.menu.Phone Numbers') }}</label>
                                                <div class="input-group">
                                                    <div class="input-group-text">
                                                        <div class="form-check">
                                                            <input type="radio" class="form-check-input phone_number"
                                                                   value="phone_number" name="originator"
                                                                   id="phone_number_check" />
                                                            <label class="form-check-label"
                                                                   for="phone_number_check"></label>
                                                        </div>
                                                    </div>
                                                    <div style="width: 17rem">
                                                        <select class="form-select select2" disabled id="phone_number"
                                                                name="phone_number">
                                                            @foreach($phone_numbers as $number)
                                                                <option value="{{ $number->number }}"> {{ $number->number }} </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan

                                    <div class="col-12">
                                        <div class="mb-1">
                                            <label class="country_code form-label"
                                                   for="country_code">{{__('locale.labels.country_code')}}</label>
                                            <select class="form-select select2" id="country_code" name="country_code">
                                                <option value="0">{{ __('locale.labels.remaining_in_number') }}</option>
                                                @foreach($coverage as $code)
                                                    <option value="{{ $code->country_id }}">
                                                        +{{ $code->country->country_code }} </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('country_code')
                                        <p><small class="text-danger">{{ $message }}</small></p>
                                        @enderror
                                    </div>

                                    <div class="col-12">

                                        <div class="mb-1">
                                            <label for="recipients"
                                                   class="form-label">{{ __('locale.labels.manual_input') }}</label>
                                            <textarea class="form-control" id="recipients" name="recipients">@if(isset($recipient))
                                                    {{ $recipient }}
                                                @endif</textarea>
                                            <p><small class="text-uppercase">
                                                    {{ __('locale.labels.total_number_of_recipients') }}:<span
                                                            class="number_of_recipients fw-bold text-success">0</span>
                                                </small></p>
                                            @error('recipients')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-1">
                                            <div class="btn-group btn-group-sm recipients" role="group">
                                                <input type="radio" class="btn-check" name="delimiter" value=","
                                                       id="comma" autocomplete="off" checked />
                                                <label class="btn btn-outline-primary" for="comma">,
                                                    ({{ __('locale.labels.comma') }})</label>

                                                <input type="radio" class="btn-check" name="delimiter" value=";"
                                                       id="semicolon" autocomplete="off" />
                                                <label class="btn btn-outline-primary" for="semicolon">;
                                                    ({{ __('locale.labels.semicolon') }})</label>

                                                <input type="radio" class="btn-check" name="delimiter" value="|"
                                                       id="bar" autocomplete="off" />
                                                <label class="btn btn-outline-primary" for="bar">|
                                                    ({{ __('locale.labels.bar') }})</label>

                                                <input type="radio" class="btn-check" name="delimiter" value="tab"
                                                       id="tab" autocomplete="off" />
                                                <label class="btn btn-outline-primary"
                                                       for="tab">{{ __('locale.labels.tab') }}</label>

                                                <input type="radio" class="btn-check" name="delimiter" value="new_line"
                                                       id="new_line" autocomplete="off" />
                                                <label class="btn btn-outline-primary"
                                                       for="new_line">{{ __('locale.labels.new_line') }}</label>

                                            </div>

                                            @error('delimiter')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>

                                        <p>
                                            <small class="text-primary">{!! __('locale.description.manual_input') !!} {!! __('locale.contacts.include_country_code_for_successful_import') !!}</small>
                                        </p>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-1">
                                            <label class="sms_template form-label">{{__('locale.permission.sms_template')}}</label>
                                            <select class="form-select select2" id="sms_template">
                                                <option>{{ __('locale.labels.select_one') }}</option>
                                                @foreach($templates as $template)
                                                    <option value="{{$template->id}}">{{ $template->name }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>

                                    @if(config('app.trai_dlt') && Auth::user()->customer->activeSubscription()->plan->is_dlt)
                                        <div class="col-12">
                                            <div class="mb-1">
                                                <label for="dlt_template_id"
                                                       class="form-label required">{{ __('locale.templates.dlt_template_id') }}</label>
                                                <input type="text"
                                                       id="dlt_template_id"
                                                       class="form-control @error('dlt_template_id') is-invalid @enderror"
                                                       name="dlt_template_id"
                                                       required>
                                                @error('dlt_template_id')
                                                <p><small class="text-danger">{{ $message }}</small></p>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <div class="mb-1">
                                            <label for="message"
                                                   class="required form-label">{{__('locale.labels.message')}}</label>
                                            <textarea class="form-control" name="message" rows="5"
                                                      id="message"></textarea>
                                            <div class="d-flex justify-content-between">
                                                <small class="text-primary text-uppercase text-start"
                                                       id="remaining">160 {{ __('locale.labels.characters_remaining') }}</small>
                                                <small class="text-primary text-uppercase text-end"
                                                       id="messages">1 {{ __('locale.labels.message') }} (s)</small>
                                            </div>
                                            @error('message')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <input type="hidden" value="otp" name="sms_type" id="sms_type">
                                        <button type="submit" class="btn btn-primary mr-1 mb-1"><i
                                                    data-feather="send"></i> {{ __('locale.buttons.send') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
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
    <script src="{{ asset(mix('js/scripts/sms-counter.js')) }}"></script>
    <script>
      $(document).ready(function() {

        $(".sender_id").on("click", function() {
          $("#sender_id").prop("disabled", !this.checked);
          $("#phone_number").prop("disabled", this.checked);
        });

        $(".phone_number").on("click", function() {
          $("#phone_number").prop("disabled", !this.checked);
          $("#sender_id").prop("disabled", this.checked);
        });


        // Basic Select2 select
        $(".select2").each(function() {
          let $this = $(this);
          $this.wrap("<div class=\"position-relative\"></div>");
          $this.select2({
            // the following code is used to disable x-scrollbar when click in select input and
            // take 100% width in responsive also
            dropdownAutoWidth: true,
            width: "100%",
            dropdownParent: $this.parent()
          });
        });


        $("#sender_id_custom").select2({
          tags: true,  // Allows new values
          placeholder: "{{ __('locale.labels.select_one_or_insert_your_own') }}",
          allowClear: true
        });


        let $remaining = $("#remaining"),
          $get_msg = $("#message"),
          $messages = $remaining.next(),
          firstInvalid = $("form").find(".is-invalid").eq(0),
          $get_recipients = $("#recipients"),
          number_of_recipients_ajax = 0,
          number_of_recipients_manual = 0;

        if (firstInvalid.length) {
          $("body, html").stop(true, true).animate({
            "scrollTop": firstInvalid.offset().top - 200 + "px"
          }, 200);
        }


        function get_character() {
          if ($get_msg[0].value !== null) {

            let data = SmsCounter.count($get_msg[0].value, true);

            $remaining.text(data.remaining + " {!! __('locale.labels.characters_remaining') !!}");
            $messages.text(data.messages + " {!! __('locale.labels.message') !!}" + "(s)");

          }

        }

        $("#sms_template").on("change", function() {

          let template_id = $(this).val();

          $.ajax({
            url: "{{ url('templates/show-data')}}" + "/" + template_id,
            type: "POST",
            data: {
              _token: "{{csrf_token()}}"
            },
            cache: false,
            success: function(data) {
              if (data.status === "success") {
                const caretPos = $get_msg[0].selectionStart;
                const textAreaTxt = $get_msg.val();
                let txtToAdd = data.message;

                $("#dlt_template_id").val(data.dlt_template_id);

                $get_msg.val(textAreaTxt.substring(0, caretPos) + txtToAdd + textAreaTxt.substring(caretPos)).val().length;

                get_character();

              } else {
                toastr["warning"](data.message, "{{ __('locale.labels.attention') }}", {
                  closeButton: true,
                  positionClass: "toast-top-right",
                  progressBar: true,
                  newestOnTop: true,
                  rtl: isRtl
                });
              }
            },
            error: function(reject) {
              if (reject.status === 422) {
                let errors = reject.responseJSON.errors;
                $.each(errors, function(key, value) {
                  toastr["warning"](value[0], "{{__('locale.labels.attention')}}", {
                    closeButton: true,
                    positionClass: "toast-top-right",
                    progressBar: true,
                    newestOnTop: true,
                    rtl: isRtl
                  });
                });
              } else {
                toastr["warning"](reject.responseJSON.message, "{{__('locale.labels.attention')}}", {
                  closeButton: true,
                  positionClass: "toast-top-right",
                  progressBar: true,
                  newestOnTop: true,
                  rtl: isRtl
                });
              }
            }
          });
        });

        $get_msg.on("change keyup paste", get_character);

        function get_delimiter() {
          return $("input[name=delimiter]:checked").val();
        }

        function get_recipients_count() {

          let recipients_value = $get_recipients[0].value.trim();

          if (recipients_value) {
            let delimiter = get_delimiter();

            if (delimiter === ";") {
              number_of_recipients_manual = recipients_value.split(";").length;
            } else if (delimiter === ",") {
              number_of_recipients_manual = recipients_value.split(",").length;
            } else if (delimiter === "|") {
              number_of_recipients_manual = recipients_value.split("|").length;
            } else if (delimiter === "tab") {
              number_of_recipients_manual = recipients_value.split(" ").length;
            } else if (delimiter === "new_line") {
              number_of_recipients_manual = recipients_value.split("\n").length;
            } else {
              number_of_recipients_manual = 0;
            }
          } else {
            number_of_recipients_manual = 0;
          }
          let total = number_of_recipients_manual + Number(number_of_recipients_ajax);

          $(".number_of_recipients").text(total);
        }

        $get_recipients.keyup(get_recipients_count);

        $("input[name='delimiter']").change(function() {
          get_recipients_count();
        });
      });
    </script>
@endsection
