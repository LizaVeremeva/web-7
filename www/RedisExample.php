<?php
class RedisExample
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Redis();
        $this->redis->connect('redis', 6379, 2.5);
    }

    public function setValue($key, $value)
    {
        return $this->redis->set($key, $value);
    }

    public function getValue($key)
    {
        return $this->redis->get($key);
    }

    public function setNewsCounter($newsId, $count)
    {
        return $this->redis->set("news:counter:$newsId", $count);
    }

    public function getNewsCounter($newsId)
    {
        return $this->redis->get("news:counter:$newsId");
    }
}