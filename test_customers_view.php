<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::first();
Auth::login($user);

$req1 = Illuminate\Http\Request::create('/customers', 'GET');
$res1 = $app->handle($req1);
echo "? Customers Page Status: " . $res1->getStatusCode() . "\n";

$req2 = Illuminate\Http\Request::create('/pos', 'GET');
$res2 = $app->handle($req2);
echo "? POS Page Status: " . $res2->getStatusCode() . "\n";
