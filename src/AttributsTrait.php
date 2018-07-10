<?php


namespace Net2h\OauthCdiscount;


trait AttributsTrait
{
    
    protected $attributes = [];

    
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    
    public function getAttribute($name, $default = null): ?string
    {
        return $this->attributes[$name] ?? $default;
    }

    
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    
    public function merge(array $attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->attributes);
    }

    
    public function offsetGet($offset): string
    {
        return $this->getAttribute($offset);
    }

    
    public function offsetSet($offset, $value)
    {
        return $this->setAttribute($offset, $value);
    }

    
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    
    public function __get($property)
    {
        return $this->getAttribute($property);
    }

    
    public function toArray(): array
    {
        return $this->getAttributes();
    }

    
    public function toJSON()
    {
        return json_encode($this->getAttributes(), JSON_UNESCAPED_UNICODE);
    }
}
