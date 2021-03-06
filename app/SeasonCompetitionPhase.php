<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SeasonCompetitionPhase extends Model
{
	public $timestamps = false;
	protected $table = 'season_competitions_phases';

    protected $fillable = ['competition_id', 'name', 'num_participants', 'mode', 'order', 'active', 'slug'];

    public function competition()
    {
        return $this->hasOne('App\SeasonCompetition', 'id', 'competition_id');
    }

    public function groups()
    {
        return $this->hasmany('App\SeasonCompetitionPhaseGroup', 'phase_id', 'id');
    }

    public function initialGroup()
    {
        return $group = $this->groups->first();
    }

    public function groups_available_participants()
    {
        $participants = $this->num_participants;
        if ($this->groups) {
            $used_participants = 0;
            foreach ($this->groups as $group) {
                $used_participants = $used_participants + $group->num_participants;
            }
            return $participants - $used_participants;
        } else {
            return $participants;
        }
    }

    public function name() {
        $competition = SeasonCompetition::find($this->competition->id);
        if ($competition->phases->count() > 1) {
            return $this->name;
        } else {
            return null;
        }
    }

    public function is_last() {
        $competition = SeasonCompetition::find($this->competition->id);
        if ($competition->phases->count() > 1) {
            $last_phase = SeasonCompetitionPhase::where('competition_id' , '=', $this->competition->id)->orderBy('order', 'desc')->first();
            if ($last_phase->id == $this->id) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
