<div
        class="modal fade text-start modal-primary"
        id="sendingServer"
        tabindex="-1"
        aria-labelledby="sendingServer"
        aria-hidden="true"
>

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendingServer">{{ __('locale.labels.sending_server') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="form" method="post" action="{{ route('customer.developer.server') }}">
                @csrf
                <div class="modal-body">

                    <div class="col-12">
                        <div class="mb-1">
                            <label class="form-label fw-bolder font-size font-small-4 mb-50" for="sendingServer">Select
                                sending server for your API Messages</label>
                            <select class="select2 form-select" name="sending_server">
                                @if($sendingServers->count())
                                    @foreach($sendingServers as $server)
                                        @if(isset($server->sendingServer))
                                            <option value="{{$server->sendingServer->id}}"
                                                    @if(Auth::user()->api_sending_server == $server->sendingServer->id) selected @endif> {{ $server->sendingServer->name }}</option>
                                        @endif
                                    @endforeach
                                @else
                                    <option>{{ __('locale.sending_servers.have_no_sending_server_to_add') }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary"><i
                                data-feather="save"></i> {{ __('locale.buttons.save') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
