<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Blogpost
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property string|null $banner
 * @property string $status
 *
 * @property User $user
 */
class Blogpost extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'banner',
        'status',
    ];

    /**
     * Get the user that owns the blogpost.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
