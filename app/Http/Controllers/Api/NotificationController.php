<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Exception;

class NotificationController extends Controller
{
    /**
     * Get table name safely (supports 'notifications' or 'tblnotifications').
     */
    protected function getTableName(): string
    {
        if (Schema::hasTable('notifications')) {
            return 'notifications';
        }
        if (Schema::hasTable('tblnotifications')) {
            return 'tblnotifications';
        }
        return 'notifications';
    }

    /**
     * Ensure the notifications table exists in database.
     */
    protected function ensureTableExists(): void
    {
        $t = $this->getTableName();
        if (!Schema::hasTable($t)) {
            DB::statement("CREATE TABLE IF NOT EXISTS `{$t}` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED DEFAULT NULL,
                `touserid` INT UNSIGNED DEFAULT NULL,
                `description` TEXT NOT NULL,
                `link` VARCHAR(500) DEFAULT NULL,
                `isread` TINYINT(1) DEFAULT 0,
                `read` TINYINT(1) DEFAULT 0,
                `date` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
    }

    /**
     * Fetch all notifications for current user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->ensureTableExists();
            $user = auth()->user();
            $tableName = $this->getTableName();

            $query = DB::table($tableName);
            if ($user && Schema::hasColumn($tableName, 'touserid')) {
                $query->where(function ($q) use ($user) {
                    $q->where('touserid', $user->id)
                      ->orWhereNull('touserid')
                      ->orWhere('touserid', 0);
                });
            }

            $notifications = $query->orderBy('id', 'desc')->limit(50)->get()->map(function ($n) {
                $isRead = false;
                if (isset($n->isread)) {
                    $isRead = (bool)$n->isread;
                } elseif (isset($n->read)) {
                    $isRead = (bool)$n->read;
                }

                return [
                    'id'          => $n->id,
                    'description' => $n->description ?? '',
                    'text'        => strip_tags($n->description ?? ''),
                    'link'        => $n->link ?? null,
                    'read'        => $isRead,
                    'isread'      => $isRead,
                    'date'        => $n->date ?? $n->created_at ?? date('Y-m-d H:i:s'),
                    'time'        => isset($n->date) ? date('M d, H:i', strtotime($n->date)) : 'Recently',
                ];
            });

            $unreadCount = $notifications->where('read', false)->count();

            return response()->json([
                'success'      => true,
                'data'         => $notifications,
                'unread_count' => $unreadCount,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => [],
                'unread_count' => 0,
            ], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        try {
            $this->ensureTableExists();
            $tableName = $this->getTableName();

            $updateData = [];
            if (Schema::hasColumn($tableName, 'read')) {
                $updateData['read'] = 1;
            }
            if (Schema::hasColumn($tableName, 'isread')) {
                $updateData['isread'] = 1;
            }

            if (!empty($updateData)) {
                DB::table($tableName)->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead($id): JsonResponse
    {
        try {
            $this->ensureTableExists();
            $tableName = $this->getTableName();

            $updateData = [];
            if (Schema::hasColumn($tableName, 'read')) {
                $updateData['read'] = 1;
            }
            if (Schema::hasColumn($tableName, 'isread')) {
                $updateData['isread'] = 1;
            }

            if (!empty($updateData)) {
                DB::table($tableName)->where('id', $id)->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new notification.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string',
        ]);

        try {
            $this->ensureTableExists();
            $tableName = $this->getTableName();
            $user = auth()->user();

            $insertData = [
                'description' => $request->input('description'),
            ];

            if (Schema::hasColumn($tableName, 'touserid')) {
                $insertData['touserid'] = $request->input('touserid', $user ? $user->id : 0);
            }
            if (Schema::hasColumn($tableName, 'user_id')) {
                $insertData['user_id'] = $user ? $user->id : null;
            }
            if (Schema::hasColumn($tableName, 'fromuserid')) {
                $insertData['fromuserid'] = $user ? $user->id : 0;
            }
            if (Schema::hasColumn($tableName, 'link')) {
                $insertData['link'] = $request->input('link');
            }
            if (Schema::hasColumn($tableName, 'isread')) {
                $insertData['isread'] = 0;
            }
            if (Schema::hasColumn($tableName, 'read')) {
                $insertData['read'] = 0;
            }
            if (Schema::hasColumn($tableName, 'date')) {
                $insertData['date'] = now();
            }
            if (Schema::hasColumn($tableName, 'created_at')) {
                $insertData['created_at'] = now();
            }
            if (Schema::hasColumn($tableName, 'updated_at')) {
                $insertData['updated_at'] = now();
            }

            $id = DB::table($tableName)->insertGetId($insertData);

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully.',
                'id'      => $id,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
