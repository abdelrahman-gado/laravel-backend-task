<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'body',
        'coverImage',
        'pinned',
        'user_id'
    ];

    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Summary of tags
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    public function postTagPivot()
    {
        return $this->hasMany(Post_Tag::class, 'post_id', 'id');
    }

    /**
     * Summary of prunable
     * @return mixed
     */
    public function prunable()
    {
        return $this::where('deleted_at', '<=', now()->subMonth());
    }


    protected function pruning()
    {
        $imagePath = str_replace('/storage/', '', $this->coverImage);
        Storage::disk('public')->delete($imagePath);
    }
}
