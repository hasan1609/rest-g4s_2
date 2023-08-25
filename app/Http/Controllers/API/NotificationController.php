<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function store($title, $body, $notificationData, $senderId, $reciveId)
    {

        $notificationLog = new NotificationLog();
        $notificationLog->judul = $title;
        $notificationLog->body = $body;
        $notificationLog->data = $notificationData;
        $notificationLog->sender_id = $senderId;
        $notificationLog->recive_id = $reciveId;
        $notificationLog->save();

        return response()->json(['message' => 'Notification log saved successfully']);
    }

    public function getById($id)
    {
        $notif = NotificationLog::where('recive_id', $id)->get();
        return $this->handleResponse('Berhasil', ['data' => $notif], Response::HTTP_OK);
    }
}
