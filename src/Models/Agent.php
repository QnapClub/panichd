<?php

namespace Kordy\Ticketit\Models;

use App\User;
use Auth;
use Kordy\Ticketit\Models\Setting;

class Agent extends User
{

    /**
     * list of all agents and returning collection
     * @param integer $cat_id
     * @return bool
     */
    public function scopeAgents($query)
    {
        return $query->where('ticketit_agent', '1')->get();
    }

    /**
     * list of all agents and returning lists array of id and name
     * @param integer $cat_id
     * @return bool
     */
    public function scopeAgentsLists($query)
    {
        return $query->where('ticketit_agent', '1')->lists('name', 'id')->toArray();
    }

    /**
     * Check if user is agent
     * @return boolean
     */
    public static function isAgent($id = null)
    {
        if (isset($id)) {
            $user = User::find($id);
            if ($user->ticketit_agent) {
                return true;
            }

            return false;
        }
        if (auth()->check()) {
            if (auth()->user()->ticketit_agent) {
                return true;
            }

        }
    }

    /**
     * Check if user is admin
     * @return boolean
     */
    public static function isAdmin()
    {
        if (auth()->check()) {
            if (in_array(auth()->user()->id, Setting::grab('admin_ids'))) {
                return true;
            }

        }
    }

    /**
     * Check if user is the assigned agent for a ticket
     * @param integer $id ticket id
     * @return boolean
     */
    public static function isAssignedAgent($id)
    {
        if (auth()->check() && Auth::user()->ticketit_agent) {
            if (Auth::user()->id == Ticket::find($id)->agent->id) {
                return true;
            }

        }
    }

    /**
     * Check if user is the owner for a ticket
     * @param integer $id ticket id
     * @return boolean
     */
    public static function isTicketOwner($id)
    {
        if (auth()->check()) {
            if (auth()->user()->id == Ticket::find($id)->user->id) {
                return true;
            }

        }
    }

    /**
     * Get related categories
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories()
    {
        return $this->belongsToMany('Kordy\Ticketit\Models\Category', 'ticketit_categories_users', 'user_id', 'category_id');
    }

    /**
     * Get related agent tickets
     *
     */
    public function agentTickets($complete = false)
    {
        if ($complete) {
            return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'agent_id')->whereNotNull('completed_at');
        } else {
            return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'agent_id')->whereNull('completed_at');
        }
    }

    /**
     * Get related user tickets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userTickets($complete = false)
    {
        if ($complete) {
            return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'user_id')->whereNotNull('completed_at');
        } else {
            return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'user_id')->whereNull('completed_at');
        }
    }

    public function tickets($complete = false)
    {
        if ($complete) {
            return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'user_id')->whereNotNull('completed_at');
        } else {
            return $this->hasMany('Kordy\Ticketit\Models\Ticket', 'user_id')->whereNull('completed_at');
        }
    }

    public function getTickets($complete = false)
    {
        $user = Agent::find(auth()->user()->id);

        if ($user->isAdmin()) {
            $tickets = $user->tickets($complete)->active();
        } elseif ($user->isAgent()) {
            $tickets = $user->agentTickets();
        } else {
            $tickets = $user->userTickets();
        }

        return $tickets;
    }

}
