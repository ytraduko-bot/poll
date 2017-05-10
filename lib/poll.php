<?php

class rex_poll extends \rex_yform_manager_dataset
{
    // translate:poll_result_always=0,translate:poll_result_ifvoted=1,translate:poll_result_never=2,poll_result_ifended=3

    public function showResult()
    {
        if ($this->showresult == 0) {
            return true;
        } elseif ($this->showresult == 2) {
            return false;
        } elseif ($this->showresult == 3 && $this->status = 0) {
            return true;
        } elseif ($this->showresult == 1 && rex_poll_user::hasVoted($this)) {
            return true;
        }
        return false;
    }

    public function getHits()
    {
        $hits = 0;
        foreach ($this->getOptions() as $option) {
            $hits = $hits + $option->getHits();
        }
        return $hits;
    }

    public function executeVote()
    {
        if ($this->status == 0) {
            return false;
        }

        if (rex_poll_user::hasVoted($this)) {
            return false;
        }

            // rex_poll[poll_id] = vote_id
            $votes = rex_request::get('rex_poll', 'array');
        if (count($votes) == 1) {
            foreach ($votes as $poll_id => $vote_id) {
                if ($poll_id == $this->id && $this->checkOptionById($vote_id)) {
                    $post = rex_poll_vote::create();
                    $post->poll_id = $this->id;
                    $post->option_id = (int) $vote_id;
                    $post->ip = $_SERVER['REMOTE_ADDR'];
                    $post->user_hash = rex_poll_user::getHash();

                    if ($post->save()) {
                    } else {
                        dump(implode('<br>', $post->getMessages()));
                    }
                }
            }

                // TODO: User wird im Moment immer gesperrt, auch wenn das Voting falsch war.
                rex_poll_user::setVoted($this);

            return true;
        }
    }

    public function checkOptionById($id)
    {
        $id = (int) $id;
        $option = rex_poll_option::get($id);
        if ($option && $option->poll_id == $this->id) {
            return true;
        }
        return false;
    }

    public function getOptions($sortedby = 'hits')
    {
        return $this->getRelatedCollection('options');
    }

    public function getOptionsSorted($sortedby = 'hits')
    {
        $options = $this->getOptions();
        // TODO: $sortedby // hits / alphanum /
        return $options;
    }
}