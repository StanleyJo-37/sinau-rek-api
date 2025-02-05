<?php

namespace App\Http\Controllers;

use App\Http\Resources\DeviceResource;
use App\Models\Device;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpMqtt\Client\Facades\MQTT;

class MqttController extends Controller
{
    //

    public function publish(Request $request) {
        try {
            $request->validate([
                'mac_address' => 'string|required|exists:devices,mac_address',
                'hmac' => 'string|required',
                'message' => 'string|required',
                'timestamp' => 'date_format:Y-m-d H:i:s|required',
                'retain' => 'boolean',
                'connection' => 'string',
            ]);

            $timestamp = strtotime($request->timestamp);
            $now = time();
            if (abs($now - $timestamp) > 60) {
                return response()->json([
                    'message' => 'Timestamp out of range'
                ], 400);
            }
            
            $device = AuthController::verifyDevice($request->mac_address, $request->hmac, $request->timestamp);
            if (! isset($device)) {
                return response()->json([
                    'message' => 'Unable to verify device.',
                ], 401);
            }

            $mqtt = MQTT::connection();
            $mqtt->publish($device->publish_topic, $request->message, $request->retain ?? false, $request->connection ?? null);
            $mqtt->disconnect();

            return response()->json([
                'status' => 'Device authenticated and messaged published.',
                'device' => new DeviceResource($device),
                'message' => $request->message,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error publishing data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function subscribe(Request $request) {
        try {
            $request->validate([
                'mac_address' => 'string|required|exists:devices,mac_address',
                'hmac' => 'string|required',
                'message' => 'string|required',
                'timestamp' => 'date_format:Y-m-d H:i:s|required',
                'retain' => 'boolean',
                'connection' => 'string',
            ]);
            
            $timestamp = strtotime($request->timestamp);
            $now = time();
            if (abs($now - $timestamp) > 60) {
                return response()->json([
                    'message' => 'Timestamp out of range'
                ], 400);
            }

            $device = AuthController::verifyDevice($request->mac_address, $request->hmac, $request->timestamp);
            if (! isset($device)) {
                return response()->json([
                    'message' => 'Unable to verify device.',
                ], 401);
            }

            $mqtt = MQTT::connection();
            $mqtt->subscribe($device->subscribe_topic, function($topic, $message) {
                echo "Received message: $message on topic: $topic";
            });
            $mqtt->loop();

            return response()->json([
                'status' => 'Server subscribed to the topic.',
                'device' => new DeviceResource($device),
                'subscribe_topic' => $device->subscribe_topic, 
                'message' => $request->message,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error publishing data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
