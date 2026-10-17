<?php

namespace App\Http\Controllers;

use App\Billing\BillingGateway;
use App\Billing\BillingSignatureException;
use App\Billing\WebhookProcessor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BillingWebhookController extends Controller
{
    public function __invoke(Request $request, BillingGateway $gateway, WebhookProcessor $processor): JsonResponse
    {
        try {
            $event = $gateway->parseWebhook(
                $request->getContent(),
                $request->header('X-Billing-Signature'),
            );
            $processor->handle($event);
        } catch (BillingSignatureException $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'Invalid billing payload.'], 400);
        }

        return response()->json(['received' => true]);
    }
}
