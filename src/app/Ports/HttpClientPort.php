<?php

namespace App\Ports;

interface HttpClientPort
{
    public function authorize(): bool;
    public function notify(): bool;
}
