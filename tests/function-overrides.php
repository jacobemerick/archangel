<?php

namespace Jacobemerick\Archangel;

function mail($to, $subject, $message, $headers) {
    return compact('to', 'subject', 'message', 'headers');
}

function phpversion() {
    return '6.0.0';
}

function uniqid() {
    return '1234567890123';
}
