<?php

use Nip\Profiler\Adapters\AbstractAdapter;
use Nip\Profiler\Profile;

/**
 * Class Nip_Profiler
 */
class Nip_Profiler
{
    public $enabled = false;

    public $profiles = [];
    public $runningProfiles = [];
    public $filterElapsedSecs = null;
    /**
     * @var AbstractAdapter[]
     */
    protected $writers = [];

    /**
     * Singleton
     *
     * @return Nip_Profiler
     */
    public static function instance()
    {
        static $instance;
        if (!($instance instanceof self)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled($enabled = false)
    {
        $this->enabled = (boolean)$enabled;
        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->profiles = [];

        return $this;
    }


    /**
     * @param bool $profileName
     * @return Profile|void
     */
    public function start($profileName = false)
    {
        if (!$this->checkEnabled()) {
            return;
        }

        $profileID = $this->newProfileID($profileName);

        $profile = $this->newProfile($profileID);
        $profile->setName($profileName);

        $this->profiles[$profileID] = $profile;
        $this->addRunningProces($profileID);
        return $profile;
    }

    /**
     * @return bool
     */
    public function checkEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $name
     * @return string
     */
    public function newProfileID($name = null)
    {
        if (empty($name)) {
            return $name;
        }
        $profilesCount = count($this->getProfiles(null, true));
        return 'profile' . $profilesCount;
    }

    /**
     * @param null $type
     * @param bool $showUnfinished
     * @return array|bool
     */
    public function getProfiles($type = null, $showUnfinished = false)
    {
        $profiles = [];
        foreach ($this->profiles as $key => $profile) {
            if ($type === null) {
                $condition = true;
            } else {
                $condition = ($profile->type && $type);
            }

            if (($profile->hasEnded() || $showUnfinished) && $condition) {
                $profiles[$key] = $profile;
            }
        }

        return $profiles;
    }

    /**
     * @param $id
     * @return Profile
     */
    public function newProfile($id)
    {
        return new Profile($id);
    }

    /**
     * @param $profileID
     */
    public function addRunningProces($profileID)
    {
        $this->runningProfiles[] = $profileID;
    }

    /**
     * @param bool $profileID
     */
    public function end($profileID = false)
    {
        $profileID = $this->endPreckeck($profileID);
        if ($profileID) {
            $profile = $this->endProfile($profileID);
            if ($this->applyFilters($profile)) {
                $this->outputWriters($profile);
            }
        }
        return;
    }

    /**
     * @param boolean $profileID
     */
    protected function endPreckeck($profileID)
    {
        if (!$this->checkEnabled()) {
            return;
        }

        if ($profileID == false) {
            $profileID = $this->getLastRunningProces();
        }
        return $profileID;
    }

    public function getLastRunningProces()
    {
        return array_pop($this->runningProfiles);
    }

    public function endProfile($profileID)
    {
        $profile = $this->getProfile($profileID);
        $profile->end();

        $key = array_search($profileID, $this->runningProfiles);
        if (is_numeric($key)) {
            unset($this->runningProfiles[$key]);
        }

        return $profile;
    }

    public function getProfile($profileID)
    {
        if (is_object($profileID)) {
            return $profileID;
        }

        if (!array_key_exists($profileID, $this->profiles)) {
            trigger_error("Profile handle '$profileID' not found in profiler log.", E_USER_ERROR);
        }

        $profile = $this->profiles[$profileID];

        return $profile;
    }

    /**
     * @param $profile
     * @return bool
     */
    protected function applyFilters($profile)
    {
        return $this->secondsFilter($profile);
    }

    /**
     * @param $profile
     * @return bool
     */
    public function secondsFilter($profile)
    {
        if ($profile && null !== $this->filterElapsedSecs && $profile->getElapsedSecs() < $this->filterElapsedSecs) {
            $this->deleteProfile($profile);
            return false;
        }
        return true;
    }

    /**
     * @param $profile
     */
    public function deleteProfile($profile)
    {
        if (!array_key_exists($profile->profileID, $this->profiles)) {
            trigger_error("Query handle '{$profile->profileID}' not found in profiler log.", E_USER_ERROR);
        }
        unset($this->profiles[$profile->profileID]);
        return;
    }

    /**
     * @param $profile
     */
    public function outputWriters($profile)
    {
        foreach ($this->writers as $writer) {
            $writer->write($profile);
        }
    }

    public function lastProcessID()
    {
        end($this->profiles);

        return key($this->profiles);
    }

    public function setFilterElapsedSecs($minimumSeconds = null)
    {
        if (null === $minimumSeconds) {
            $this->filterElapsedSecs = null;
        } else {
            $this->filterElapsedSecs = (integer)$minimumSeconds;
        }

        return $this;
    }

    /**
     * @param AbstractAdapter $writer
     */
    public function addWriter(AbstractAdapter $writer)
    {
        $this->writers[] = $writer;
    }

    /**
     * @param string $name
     * @return AbstractAdapter
     */
    public function newWriter($name)
    {
        $class = $this->newWriterClass($name);
        $writer = new $class();
        return $writer;
    }

    /**
     * @param $name
     * @return string
     */
    public function newWriterClass($name)
    {
        return 'Nip\Profiler\Adapters\\' . ucfirst($name);
    }
}
