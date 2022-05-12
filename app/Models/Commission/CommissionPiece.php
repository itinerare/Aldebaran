<?php

namespace App\Models\Commission;

use App\Models\Gallery\Piece;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionPiece extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'commission_id', 'piece_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commission_pieces';

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**
     * Validation rules for submission creation.
     *
     * @var array
     */
    public static $createRules = [
        //
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the piece associated with this commission piece.
     */
    public function piece()
    {
        return $this->belongsTo(Piece::class, 'piece_id');
    }
}
