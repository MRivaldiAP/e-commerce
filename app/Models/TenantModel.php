<?php

namespace App\Models;

use App\Models\Concerns\UsesApplicationConnection;
use Illuminate\Database\Eloquent\Model;

abstract class TenantModel extends Model
{
    use UsesApplicationConnection;
}
