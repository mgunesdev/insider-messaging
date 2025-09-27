<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/messages/sent",
     *      operationId="getSentMessages",
     *      tags={"Messages"},
     *      summary="Get list of sent messages",
     *      description="Returns provider messageIds of sent messages",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="to", type="string", example="+905555555555"),
     *                      @OA\Property(property="messageId", type="string", example="abc-123"),
     *                      @OA\Property(property="sent_at", type="string", format="date-time", example="2025-09-27T11:20:31Z")
     *                  )
     *              )
     *          )
     *      )
     * )
     */
    public function sent(Request $request)
    {
        $items = Message::where('status','sent')
            ->latest('sent_at')
            ->limit((int) $request->get('limit', 100))
            ->get(['id','to','provider_message_id','sent_at']);

        // Sadece messageId listesi istiyorsan:
        // return response()->json($items->pluck('provider_message_id'));

        return response()->json([
            'data' => $items->map(fn($m)=>[
                'id' => $m->id,
                'to' => $m->to,
                'messageId' => $m->provider_message_id,
                'sent_at' => optional($m->sent_at)->toIso8601String(),
            ])
        ]);
    }
}
