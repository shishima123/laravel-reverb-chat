<?php

namespace App\Http\Controllers;

use App\Events\ChatSendEvent;
use App\Http\Requests\ChatRequest;
use App\Models\Chat;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $chats = Chat::with('user')->latest('id')->cursorPaginate(10);

        if ($request->ajax()) {
            return response()->json($chats);
        }

        return view('home', compact('chats'));
    }

    public function store(ChatRequest $request)
    {
        $chat = Chat::create([
            'user_id' => auth()->user()->id,
            'message' => $request->message,
            'created_at' => Carbon::parse($request->created_at)
        ]);

        broadcast(new ChatSendEvent(chat: $chat, roomId: 'dashboard'))->toOthers();

        return $chat;
    }
}
