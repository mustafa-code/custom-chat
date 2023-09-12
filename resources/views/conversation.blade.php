<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ChatifySite</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-P50sG3Aznjr6PJg3qjGk4O9KXLJvKxFtuoPh6Q7oUARLl1mX2Kcb2FA5tgm1PyDDl2UzOM4Ytivfp5C67FshGGw==" crossorigin="anonymous" />
    <!-- <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" /> -->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

</head>

<body class="antialiased">
    <section class="flex relative bg-[#f5f5f5] items-center justify-center min-h-screen">
        <div class="relative items-center w-full px-5 mx-auto max-w-7xl md:px-12">
            <div class="text-center">
                <p class="w-auto">
                    <a href="/chat" class="font-semibold text-[#4354ff] text-sm uppercase">Chat Index</a>
                </p>
                <div class="p-2 pb-6 max-w-lg mx-auto">
                    <p class="text-lg"><span class="text-gray-800 font-medium">{{$chat->title}}</span></p>
                    <p class="truncate"><a href="{{$chat->id}}" class="text-blue-500">{{$chat->id}}</a></p>
                </div>
            </div>
            <div class="max-w-lg mx-auto mt-4">
                <div class="relative flex items-start p-4 space-x-3 bg-white shadow group rounded-2xl">
                    <div class="flex-1 min-w-0">
                        <div class="pb-10 space-y-4 h-[60vh] overflow-scroll" id="messages">
                            @foreach($messages as $message)
                            @if($message->role == "user")
                            <div class="ml-16 flex justify-end">
                                <di class="bg-gray-100 p-3 rounded-md">
                                    <p class="font-medium text-blue-500 text-right text-sm">Question</p>
                                    <hr class="my-2" />
                                    <p class="text-gray-800">{{$message->content}}</p>
                                </di>
                            </div>
                            @else
                            <div style="display: flex;align-items: center;">
                                <div class="bg-gray-100 p-2 rounded-md" style="width: 75%;">
                                    <p class="font-medium text-blue-500 text-sm">Answer</p>
                                    <hr class="my-2" />
                                    <p class="text-gray-800">{{$message->content}}</p>
                                </div>
                                <span style="margin: 0 12px;">
                                    <a href=""><i class="fa-solid fa-thumbs-down"></i></a>
                                </span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        <form class="flex gap-2 pt-2" id="form-question">
                            @csrf
                            <input type="hidden" name="_chat_id" value="{{$chat->id}}" />
                            <input placeholder="Ask any question!" class="w-full p-2 rounded-md border border-gray-600 focus:outline-none" name="question" />
                            <button id="btn-submit-question" type="submit" class="bg-black text-white shadow px-3 rounded-md flex items-center">Send</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>

</html>