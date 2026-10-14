<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Moda extends Model
{
    protected $table = 'moda';
    protected $primaryKey = 'id_moda';
    public $timestamps = false;
    protected $fillable = ['nama_moda'];

    public function users(): HasMany { return $this->hasMany(User::class, 'id_moda', 'id_moda'); }
    public function ruteSubsidi(): HasMany { return $this->hasMany(RuteSubsidi::class, 'id_moda', 'id_moda'); }
}
