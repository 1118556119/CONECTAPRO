<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Enviar notificación a un usuario
     *
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @return Notification
     */
    public function send(User $user, string $type, string $title, string $message, array $data = [])
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Enviar notificación a múltiples usuarios
     *
     * @param array $userIds
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    public function sendToMany(array $userIds, string $type, string $title, string $message, array $data = [])
    {
        $notifications = [];
        
        foreach ($userIds as $userId) {
            $notifications[] = Notification::create([
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
        }
        
        return $notifications;
    }
}