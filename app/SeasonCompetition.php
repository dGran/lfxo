<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeasonCompetition extends Model
{
	public $timestamps = false;

    protected $fillable = [
        'season_id', 'name', 'img', 'stats_mvp', 'stats_goals', 'stats_assists', 'stats_yellow_cards', 'stats_red_cards', 'slug'
    ];

    public function season()
    {
        return $this->hasOne('App\Season', 'id', 'season_id');
    }

    public function phases()
    {
        return $this->hasmany('App\SeasonCompetitionPhase', 'competition_id', 'id');
    }

    public function has_stats()
    {
        if ($this->stats_mvp || $this->stats_goals || $this->stats_assists || $this->stats_yellow_cards || $this->stats_red_cards) {
            return true;
        } else {
            return false;
        }
    }

    public function initialPhase()
    {
        return $phase = $this->phases->first();
    }

    public function scopeSeasonId($query, $seasonID)
    {
        if (trim($seasonID) !="") {
            $query->where("season_id", "=", $seasonID);
        }
    }

    public function isLocalImg() {
        if (starts_with($this->img, 'img/competitions/')) {
            return true;
        }
        return false;
    }

    public function getImgFormatted() {
        if ($this->img) {
            $img = $this->img;
            $local_img = asset($this->img);
            $broken = asset('img/broken.png');

            if ($this->isLocalImg()) {
                if (file_exists($img)) {
                    return $local_img;
                } else {
                    return $broken;
                }
            } else {
                // if (validateUrl($img)) {
                if (@GetImageSize($img)) {
                    return $img;
                } else {
                    return $broken;
                }
            }
        } else {
            $no_img = asset('img/no-photo.png');
            return $no_img;
        }

    }

    public function isConfigured() {
        if ($this->phases->count() > 0) {
            if ($this->initialPhase()->groups->count() > 0) {
                if ($this->initialPhase()->active) {
                    if ($this->initialPhase()->mode == 'league') {
                        if ($this->initialPhase()->initialGroup()->league) {
                            if ($this->initialPhase()->initialGroup()->league->days->count() > 0) {
                                return true;
                            }
                        }
                    } else {
                        if ($this->initialPhase()->initialGroup()->playoff) {
                            if ($this->initialPhase()->initialGroup()->playoff->rounds->count() > 0) {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}
