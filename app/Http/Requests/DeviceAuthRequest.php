<?php

namespace App\Http\Requests;

use App\Models\Device;
use Illuminate\Foundation\Http\FormRequest;

class DeviceAuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'mac_address' => 'string|required|exists:devices,mac_address',
            'hmac' => 'string|required',
            'timestamp' => 'required',
        ];
    }

    /**
     * Get the device credentials.
     *
     * @return array<string>
     */
    public function credentials(): array
    {
        return $this->only('mac_address', 'hmac', 'timestamp');
    }

    /**
     * Authenticate the user.
     *
     * @return bool
     */
    public function authenticate(): bool
    {
        $credentials = $this->validated();
        $device = Device::where('mac_address', $credentials['mac_address'])->first();

        if (! isset($device)) {
            $this->failedLogin();
        return false;
        }

        $message = $credentials['timestamp'];

    $expectedHMAC = hash_hmac('sha256', $message, $device->secret_key);
        if (! hash_equals($expectedHMAC, $credentials['hmac'])) {
            $this->failedLogin();
            return false;
        }

        return true;
    }
}
