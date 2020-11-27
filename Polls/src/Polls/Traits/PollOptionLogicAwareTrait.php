<?php

namespace Polls\Traits;

trait PollOptionLogicAwareTrait
{

    /**
     * Get server public path
     * 
     * @return string
     */
    public function getServerPublicPath()
    {
        return str_replace('/', DS, sprintf('%s/public/', ROOT_PATH));
    }

    /**
     * Get image directory
     * 
     * @return string
     */
    public function getImageDirectory()
    {
        $date = new \DateTime('now');

        $path = str_replace('/', DS, sprintf(
            '%smedia/polls/%s/%s/%s',
            $this->getServerPublicPath(), $date->format('Y'), $date->format('m'), $date->format('d')
        ));

        return $path;
    }

    /**
     * Get image public path
     * 
     * @param string $subfolder
     * @return string
     */
    public function getImagePublicPath()
    {
        if ($this->imagePath !== '') {
            $orignalPublicPath = DS . str_replace($this->getServerPublicPath(), '', $this->imagePath);
            $orignalPublicPath = str_replace(DS, '/', $orignalPublicPath);

            return $orignalPublicPath;
        }

        return '';
    }

    public function increaseVotesCount()
    {
        $this->votesCount = $this->votesCount + 1;

        return $this;
    }

    public function decreaseVotesCount()
    {
        if ($this->votesCount > 0) {
            $this->votesCount = $this->votesCount - 1;
        }

        return $this;
    }

}
