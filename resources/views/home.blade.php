@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <!-- DIRECT CHAT SUCCESS -->
                <div class="box box-success direct-chat direct-chat-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Customer Support</h3>

                        <div class="box-tools pull-right">
                            <span data-toggle="tooltip" title="3 New Messages" class="badge bg-green">3</span>
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-toggle="tooltip" title="Contacts" data-widget="chat-pane-toggle">
                                <i class="fa fa-comments"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <!-- Conversations are loaded here -->
                        <div id="directMessageSection" class="direct-chat-messages">
                            @foreach(collect($chats->items())->sortBy('id') as $chat)
                                @if($chat->user->is(auth()->user()))
                                    <!-- Message to the right -->
                                    <div class="direct-chat-msg-section right">
                                        <div class="direct-chat-info">
                                            <span class="direct-chat-name pull-right">{{ $chat->user->name ?? ''}}</span>
                                            <span class="direct-chat-timestamp pull-left">{{ $chat->created_at }}</span>
                                        </div>
                                        <!-- /.direct-chat-info -->
                                        <div class="direct-chat-message">
                                            <img class="direct-chat-img" src="https://bootdey.com/img/Content/user_2.jpg" alt="Message User Image"><!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                {{ $chat->message ?? '' }}
                                            </div>
                                        </div>
                                        <!-- /.direct-chat-text -->
                                    </div>
                                    <!-- /.direct-chat-msg-section -->
                                @else
                                    <!-- Message. Default to the left -->
                                    <div class="direct-chat-msg-section">
                                        <div class="direct-chat-info">
                                            <span class="direct-chat-name pull-left">{{ $chat->user->name ?? ''}}</span>
                                            <span class="direct-chat-timestamp pull-right">{{ $chat->created_at }}</span>
                                        </div>
                                        <!-- /.direct-chat-info -->
                                        <div class="direct-chat-message">
                                            <img class="direct-chat-img" src="https://bootdey.com/img/Content/user_1.jpg" alt="Message User Image"><!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                {{ $chat->message ?? '' }}
                                            </div>
                                        </div>
                                        <!-- /.direct-chat-text -->
                                    </div>
                                    <!-- /.direct-chat-msg-section -->
                                @endif
                            @endforeach
                            <input id="next_url" type="hidden" name="next_url" value="{{ $chats->nextPageUrl() }}">
                        </div>
                        <!--/.direct-chat-messages-->
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <form id="message_form" action="{{ route('store') }}" method="POST" enctype="multipart/form-data">
                            @method('POST')
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" placeholder="Type Message ..." class="form-control">
                                <span class="input-group-btn">
                                <button type="submit" class="btn btn-success btn-flat ms-4">Send</button>
                            </span>
                            </div>
                        </form>
                    </div>
                    <!-- /.box-footer-->
                </div>
                <!--/.direct-chat -->
            </div>
        </div>
    </div>

    <script type="module">
        $(function () {
            const $directMessageSection = $('#directMessageSection')
            const user = {!! json_encode(auth()->user()) !!};
            let isLoadMoreProcessing = false;

            scrollLastChat()

            handleLoadMoreChat()

            handleSubmitChat()

            Echo.join(`chat-box.dashboard`)
                .listen('ChatSendEvent', (e) => {
                    let shouldScrollToLast = false;

                    // scroll to bottom if scroll in the last item
                    if ($directMessageSection[0].scrollHeight - $directMessageSection.scrollTop() < $directMessageSection.outerHeight() + 10) {
                        shouldScrollToLast = true;
                    }
                    appendMessageLeft(e.chat)

                    if (shouldScrollToLast) {
                        scrollLastChat()
                    }
                })

            function appendMessageLeft(chat) {
                return renderMessage(chat, 'left', 'append')
            }

            function appendMessageRight(chat) {
                return renderMessage(chat, 'right', 'append')
            }

            function prependMessageLeft(chat) {
                return renderMessage(chat, 'left', 'prepend')
            }

            function prependMessageRight(chat) {
                return renderMessage(chat, 'right', 'prepend')
            }

            function renderMessage(chat, side, type) {
                let avatar = side === 'left' ? 'https://bootdey.com/img/Content/user_1.jpg' : 'https://bootdey.com/img/Content/user_2.jpg'
                let html = `
                        <div class="direct-chat-msg-section ${side}">
                            <div class="direct-chat-info">
                                <span class="direct-chat-name pull-right">${chat?.user?.name || ''}</span>
                                <span class="direct-chat-timestamp pull-left">${chat?.created_at || ''}</span>
                            </div>
                            <!-- /.direct-chat-info -->
                            <div class="direct-chat-message">
                                <img class="direct-chat-img" src="${avatar}" alt="Message User Image"><!-- /.direct-chat-img -->
                                <div class="direct-chat-text">
                                    ${chat?.message || ''}
                                </div>
                            </div>
                        </div>`

                if (type === 'append') {
                    $directMessageSection.append(html)
                } else {
                    $directMessageSection.prepend(html)
                }
            }

            function scrollLastChat() {
                $directMessageSection[0].scrollTop = $directMessageSection[0].scrollHeight;
            }

            function handleLoadMoreChat() {
                $directMessageSection.scroll(function () {
                    let $next_url = $('#next_url')
                    let url = $next_url.val()

                    if ($(this).scrollTop() == 0) {
                        if (!isLoadMoreProcessing && url) {
                            isLoadMoreProcessing = true
                            $.ajax({
                                type: 'GET',
                                url: url,
                                dataType: 'JSON',
                                contentType: false,
                                cache: false,
                                processData: false,
                                success: function (chats) {
                                    console.log(chats.next_page_url);
                                    $next_url.val(chats.next_page_url)
                                    chats.data.forEach((chat) => {
                                        console.log(chat);
                                        if (chat.user_id == user.id) {
                                            prependMessageRight(chat)
                                        } else {
                                            prependMessageLeft(chat)
                                        }
                                    })
                                },
                                error: function (e) {
                                    console.log(e);
                                },
                                complete: function () {
                                    isLoadMoreProcessing = false
                                }
                            });
                        }
                    }
                })
            }

            function handleSubmitChat() {
                $('#message_form').on('submit', function (event) {
                    event.preventDefault()

                    let created_at = moment().format('YYYY-MM-DD HH:m:s')
                    let $message = $(this).find('[name="message"]')

                    let data = {
                        message: $message.val(),
                        created_at: created_at,
                        user: user
                    }

                    appendMessageRight(data)
                    scrollLastChat()

                    let formData = new FormData(this)
                    formData.append('created_at', created_at)

                    $message.val('')

                    $.ajax({
                        headers: {
                            "X-Socket-Id": Echo.socketId(),
                        },
                        type: 'POST',
                        url: '/',
                        dataType: 'JSON',
                        contentType: false,
                        cache: false,
                        processData: false,
                        data: formData,
                        success: function (data) {
                            // appendMessageRight(data)
                        },
                        error: function (e) {
                            console.log(e);
                        }
                    });
                })
            }
        })
    </script>
@endsection
