@extends('layouts/contentLayoutMaster')

@section('title', __('locale.menu.Quick Send'))

@section('vendor-style')
    <!-- vendor css files -->
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/forms/select/select2.min.css')) }}">
@endsection

@section('page-style')

    <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/ui/iphone.css')) }}">

    <style>
        .customized_select2 .select2-selection--single, .input_sender_id {
            border-left: 0;
            border-radius: 0 4px 4px 0;
            min-height: 2.75rem !important;
        }

        .input-group > div.position-relative {
            flex-grow: 1;
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

                            <form id="form-send" class="form form-vertical"
                                  action="{{ route('customer.sms.quick_send') }}"
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
                                                        @if(isset($server->sendingServer) && $server->sendingServer->status == 1 && $server->sendingServer->plain)
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
                                                       class="form-label">{{ __('locale.menu.Phone Numbers') }}
                                                    <a class="text-success text-decoration-underline mx-1 text-uppercase cursor-pointer text"
                                                       href="{{ route('customer.numbers.buy') }}"
                                                       target="__blank">{{ __('locale.labels.request_new') }}</a>
                                                </label>
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
                                                @foreach($coverage as $code)
                                                    <option value="{{ $code->country_id }}"
                                                            @if($code->country->country_code == $countryCode) selected @endif>
                                                        +{{ $code->country->country_code }} </option>
                                                @endforeach
                                                <option value="0">{{ __('locale.labels.multiple_self') }}</option>
                                            </select>
                                        </div>
                                        @error('country_code')
                                        <p><small class="text-danger">{{ $message }}</small></p>
                                        @enderror
                                    </div>

                                    <div class="col-12">

                                        <div class="mb-1">
                                            <label for="recipients"
                                                   class="form-label">{{ __('locale.labels.recipients') }}:
                                                @can('campaign_builder')
                                                    <small class="text-primary">{!! __('locale.description.manual_input') !!}  </small>
                                                    <a class="text-success text-uppercase text-decoration-underline"
                                                       href="{{route('customer.sms.campaign_builder')}}">{{ __('locale.menu.Campaign Builder') }}</a>
                                                    <small class="text-primary">{!! __('locale.contacts.include_country_code_for_successful_import') !!}</small>
                                                @else
                                                    <small class="text-primary">Note: You can send a maximum of <code>100</code>
                                                        rows by copy-pasting.</small>
                                                @endcan

                                            </label>
                                            <textarea class="form-control" id="recipients" name="recipients">@if(isset($recipient))
                                                    {{ $recipient }}
                                                @endif</textarea>
                                            <div class="row mt-1">
                                                <div class="col-md-7 col-12">
                                                    <div class="btn-group btn-group-sm recipients" role="group">
                                                        <input type="radio" class="btn-check" name="delimiter" value=","
                                                               id="comma" autocomplete="off" checked />
                                                        <label class="btn btn-outline-primary" for="comma">,
                                                            ({{ __('locale.labels.comma') }})</label>

                                                        <input type="radio" class="btn-check" name="delimiter" value=";"
                                                               id="semicolon" autocomplete="off" />
                                                        <label class="btn btn-outline-primary" for="semicolon">;
                                                            ({{ __('locale.labels.semicolon') }})</label>

                                                        <input type="radio" class="btn-check" name="delimiter"
                                                               value="new_line"
                                                               id="new_line" autocomplete="off" />
                                                        <label class="btn btn-outline-primary"
                                                               for="new_line">{{ __('locale.labels.new_line') }}</label>

                                                    </div>

                                                    @error('delimiter')
                                                    <p><small class="text-danger">{{ $message }}</small></p>
                                                    @enderror
                                                </div>
                                                <div class="col-md-5 col-12 d-flex justify-content-md-end">
                                                    <small class="text-uppercase">
                                                        {{ __('locale.labels.total_number_of_recipients') }}:<span
                                                                class="number_of_recipients fw-bold text-success">0</span>
                                                    </small></div>
                                                @error('recipients')
                                                <p><small class="text-danger">{{ $message }}</small></p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-12">
                                        <div class="mb-1">
                                            <label class="sms_template form-label">{{__('locale.permission.sms_template')}}
                                                ({{ __('locale.labels.optional') }})</label>
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
                                                   class="required form-label">{{ __('locale.labels.message') }}</label>
                                            <textarea placeholder="{{ __('locale.labels.type_message') }}"
                                                      class="form-control" name="message" rows="5"
                                                      id="message"></textarea>


                                            <div class="d-flex justify-content-between">
                                                <small class="text-primary text-uppercase">
                                                    {{ __('locale.labels.remaining') }} : <span
                                                            id="remaining">160</span>
                                                    ( <span class="text-success"
                                                            id="charCount"> 0 </span>&nbsp;{{ __('locale.labels.characters') }}
                                                    )
                                                </small>
                                                <small class="text-primary text-uppercase">
                                                    {{ __('locale.labels.message') }}(s) : <span id="messages">1</span>
                                                    ({{ __('locale.labels.encoding') }} : <span class="text-success"
                                                                                                id="encoding">GSM_7BIT</span>)
                                                </small>
                                            </div>
                                            @error('message')
                                            <p><small class="text-danger">{{ $message }}</small></p>
                                            @enderror
                                        </div>
                                    </div>

                                </div>

                                <div class="d-flex justify-content-between">
                                    <div class="d-none d-sm-block">
                                        <button type="button" id="phoneMessagePreview" class="btn btn-info mr-1 mb-1"><i
                                                    data-feather="smartphone"></i> {{ __('locale.buttons.preview') }}
                                        </button>
                                    </div>
                                    <div class="">
                                        <input type="hidden" value="plain" name="sms_type" id="sms_type">
                                        <button type="button" id="sendMessagePreview"
                                                class="btn btn-primary mr-1 mb-1"><i data-feather="send"></i>
                                            {{ __('locale.buttons.send') }}</button>
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


    <!-- Mobile Preview Modal -->
    @include('customer.Campaigns._mobilePreviewModal')

    <!-- message preview Modal -->
    @include('customer.Campaigns._messagePreviewModal')
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
          let $this = $(this),
            $placeholder = "{{ __('locale.labels.select_one') }}",
            $allowClear = false;
          $this.wrap("<div class=\"position-relative\"></div>");
          if ($this.prop("multiple")) {
            $placeholder = "{{ __('locale.labels.select_one_or_more') }}";
            $allowClear = true;
          }
          $this.select2({
            // the following code is used to disable x-scrollbar when click in select input and
            // take 100% width in responsive also
            dropdownAutoWidth: true,
            width: "100%",
            dropdownParent: $this.parent(),
            placeholder: $placeholder,
            allowClear: $allowClear
          });
        });


        $("#sender_id_custom").select2({
          tags: true,  // Allows new values
          placeholder: "{{ __('locale.labels.select_one_or_insert_your_own') }}",
          allowClear: true
        });

        let $remaining = $("#remaining"),
          $char_count = $("#charCount"),
          $encoding = $("#encoding"),
          $get_msg = $("#message"),
          $messages = $("#messages"),
          firstInvalid = $("form").find(".is-invalid").eq(0),
          $get_recipients = $("#recipients"),
          number_of_recipients_ajax = 0,
          number_of_recipients_manual = 0;

        // Calculate number of recipients
        get_recipients_count();

        //Calculate the message length
        get_character();

        if (firstInvalid.length) {
          $("body, html").stop(true, true).animate({
            "scrollTop": firstInvalid.offset().top - 200 + "px"
          }, 200);
        }

        function isArabic(text) {
          let pattern = /[\u0600-\u06FF\u0750-\u077F]/;
          return pattern.test(text);
        }

        function get_character() {
          if ($get_msg[0].value !== null) {

            let data = SmsCounter.count($get_msg[0].value, true);

            if (data.encoding === "UTF16") {
              $("#sms_type").val("unicode").trigger("change");
              if (isArabic($(this).val())) {
                $get_msg.css("direction", "rtl");
              }
            } else {
              $("#sms_type").val("plain").trigger("change");
              $get_msg.css("direction", "ltr");
            }

            $char_count.text(data.length);
            $remaining.text(data.remaining + " / " + data.per_message);
            $messages.text(data.messages);
            $encoding.text(data.encoding);

          }

        }

        $("#sms_template").on("change", function() {

          let template_id = $(this).val();
          $get_msg.val(""); // Clear the textarea content

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
          return total;
        }

        $get_recipients.on("change keyup paste", get_recipients_count);

        $("input[name='delimiter']").change(function() {
          get_recipients_count();
        });

        $("#phoneMessagePreview").on("click", function() {
          const msg = $("#message").val();
          $("#senderid").html($("#sender_id").val());
          $("#messageto").html(msg);
          $("#phonePreview").modal("show");
        });

        $("#sendMessagePreview").on("click", function() {
          let msgData = SmsCounter.count($get_msg.val(), true),
            senderId = $("#sender_id"),
            recipients = $("#recipients"),
            message = $get_msg,
            msgCount = msgData.messages,
            msgLength = msgData.length,
            msg = $get_msg.val();
          $("#msgLength").html(msgLength);
          $("#msgCost").html(msgCount);
          $("#msgRecepients").html(get_recipients_count());
          $("#msg").html(msg);

          // validate fields
          if (get_recipients_count < 1 || message.val().length < 1) {
            toastr["warning"]("{{ __('locale.auth.insert_required_fields') }}",
              "{{ __('locale.labels.attention') }}", {
                closeButton: true,
                positionClass: "toast-top-right",
                progressBar: true,
                newestOnTop: true,
                rtl: isRtl
              });
            return;
          }
          $("#messagePreview").modal("show");
        });

        $("#finalSend").on("click", function(e) {
          e.preventDefault();

          // Disable the button to prevent multiple clicks
          $("#finalSend").attr("disabled", true);

          let form = $("form#form-send");
          $(this).html($(this).data("loading-text"));
          feather.replace();
          form.submit();
        });

        //Make mobile preview time lively
        setInterval(function() {
          let date = new Date();
          let hours = date.getHours() < 10 ? "0" + date.getHours() : date.getHours();
          let minutes = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes();
          let seconds = date.getSeconds() < 10 ? "0" + date.getSeconds() : date.getSeconds();
          $(".top-section-time").html(
            hours + ":" + minutes + ":" + seconds
          );
        }, 500);
      });
    </script>
@endsection
