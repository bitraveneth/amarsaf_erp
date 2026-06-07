<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>POD Delivery #{{ $delivery->id }}</title></head>
<body>
<h1>Proof of delivery</h1>
<p>Delivery #{{ $delivery->id }} · Order #{{ $delivery->order_id }}</p>
<p>Agent: {{ $delivery->order?->agent?->name }}</p>
<p>Status: {{ $delivery->status }}</p>
@if($delivery->pod)
<p>Signed by: {{ $delivery->pod->signed_by }}</p>
<p>Receiver: {{ $delivery->pod->receiver_name }} ({{ $delivery->pod->receiver_phone }})</p>
<p>Delivered at: {{ $delivery->pod->delivered_at }}</p>
@endif
</body></html>
