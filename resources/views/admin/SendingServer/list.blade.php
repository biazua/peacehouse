@extends('layouts/contentLayoutMaster')

@section('title', __('locale.menu.Sending Servers'))

@section('vendor-style')
    {{-- vendor css files --}}
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/responsive.bootstrap5.min.css')) }}">
    <link rel="stylesheet" href="{{ asset(mix('vendors/css/tables/datatable/buttons.bootstrap5.min.css')) }}">

@endsection

@section('content')

    <!-- Basic table -->
    <section id="datatables-basic">
        <div class="mb-3 mt-2">

            @can('create sending_servers')
                <div class="btn-group">
                    <a href="{{route('admin.sending-servers.create', ['type' => 'custom'])}}"
                       class="btn btn-primary waves-light waves-effect fw-bold mx-1"> {{__('locale.plans.create_own_sending_server')}}
                        <i data-feather="plus-circle"></i></a>
                </div>
            @endcan


        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <table class="table datatables-basic">
                        <thead>
                        <tr>
                            <th></th>
                            <th>{{__('locale.labels.name')}}</th>
                            <th>{{__('locale.labels.type')}}</th>
                            <th>{{__('locale.labels.actions')}}</th>
                        </tr>
                        </thead>
                        <tbody>


                        @foreach ($sending_servers as $key => $server)
                            @php

                                $return_data = '';
                                if ($server["plain"] === true) {
                                    $return_data .= '<span class="badge bg-primary text-uppercase me-1"><span>' . __('locale.labels.sms') . '</span></span>';
                                }

                                if ($server["schedule"] === true) {
                                    $return_data .= '<span class="badge bg-success text-uppercase me-1"><span>' . __('locale.labels.schedule') . '</span></span>';
                                }

                                if ($server["two_way"] === true) {
                                    $return_data .= '<span class="badge bg-info text-uppercase me-1"><span>' . __('locale.labels.two_way') . '</span></span>';
                                }

                                if ($server["voice"] === true) {
                                    $return_data .= '<span class="badge bg-secondary text-uppercase me-1"><span>' . __('locale.labels.voice') . '</span></span>';
                                }
                                if ($server["mms"] === true) {
                                    $return_data .= '<span class="badge bg-warning text-uppercase me-1"><span>' . __('locale.labels.mms') . '</span></span>';
                                }
                                if ($server["whatsapp"] === true) {
                                    $return_data .= '<span class="badge bg-danger text-uppercase me-1"><span>' . __('locale.labels.whatsapp') . '</span></span>';
                                }

                                if ($server["viber"] === true) {
                                    $return_data .= '<span class="badge bg-dark text-uppercase me-1"><span>' . __('locale.menu.Viber') . '</span></span>';
                                }

                                if ($server["otp"] === true) {
                                    $return_data .= '<span class="badge bg-danger text-uppercase"><span>' . __('locale.menu.OTP') . '</span></span>';
                                }
                            @endphp



                            <tr>
                                <td></td>
                                <td>{{ $server['name'] }}</td>
                                <td>
                                    {!! $return_data !!}
                                </td>
                                <td>
                                    <a href="{{route('admin.sending-servers.create', ['type' => $key])}}"
                                       class="btn btn-primary btn-sm">{{__('locale.labels.choose')}}</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!--/ Basic table -->

@endsection


@section('vendor-script')
    {{-- vendor files --}}
    <script src="{{ asset(mix('vendors/js/tables/datatable/jquery.dataTables.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/dataTables.responsive.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/responsive.bootstrap5.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/datatables.buttons.min.js')) }}"></script>
    <script src="{{ asset(mix('vendors/js/tables/datatable/buttons.html5.min.js')) }}"></script>

@endsection
@section('page-script')
    {{-- Page js files --}}
    <script>
        $(document).ready(function () {
            "use strict"

            $('.datatables-basic').DataTable({

                "processing": true,
                "columns": [
                    {"data": "id", orderable: false, searchable: false},
                    {"data": "name"},
                    {"data": "type"},
                    {"data": "action", orderable: false, searchable: false}
                ],
                columnDefs: [
                    {
                        // For Responsive
                        className: 'control',
                        orderable: false,
                        responsivePriority: 2,
                        targets: 0
                    },
                ],
                dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',

                language: {
                    paginate: {
                        // remove previous & next text from pagination
                        previous: '&nbsp;',
                        next: '&nbsp;'
                    },
                    sLengthMenu: "_MENU_",
                    sZeroRecords: "{{ __('locale.datatables.no_results') }}",
                    sSearch: "{{ __('locale.datatables.search') }}",
                    sProcessing: "{{ __('locale.datatables.processing') }}",
                    sInfo: "{{ __('locale.datatables.showing_entries', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}"
                },
                responsive: {
                    details: {
                        display: $.fn.dataTable.Responsive.display.modal({
                            header: function (row) {
                                let data = row.data();
                                return 'Details of ' + data['name'];
                            }
                        }),
                        type: 'column',
                        renderer: function (api, rowIdx, columns) {
                            let data = $.map(columns, function (col) {
                                return col.title !== '' // ? Do not show row in modal popup if title is blank (for check box)
                                    ? '<tr data-dt-row="' +
                                    col.rowIdx +
                                    '" data-dt-column="' +
                                    col.columnIndex +
                                    '">' +
                                    '<td>' +
                                    col.title +
                                    ':' +
                                    '</td> ' +
                                    '<td>' +
                                    col.data +
                                    '</td>' +
                                    '</tr>'
                                    : '';
                            }).join('');

                            return data ? $('<table class="table"/>').append('<tbody>' + data + '</tbody>') : false;
                        }
                    }
                },
                aLengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],

                order: [[0, "desc"]],
                displayLength: 10,
            });

        });
    </script>

@endsection
