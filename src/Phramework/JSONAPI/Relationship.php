<?php

namespace \Phramework\JSONAPI;

/**
 * JSONAPI relationship class
 */
class Relationship
{

    const TYPE_TO_ONE  = 1;
    const TYPE_TO_MANY = 2;

    /**
     * The type of relationship from this resource to relationship resource
     * @var Relationship::TYPE_TO_ONE|Relationship::TYPE_TO_MANY
     */
    protected $relationshipType;

    /**
     * Attribute of resource, this attribute is related to resource(s)
     * of type $type
     * @var string
     */
    protected $attribute;

    /**
     * Relationship's resource type
     * @var string
     */
    protected $relationshipResourceType;

    /**
     * Relationship's class
     * @var string|null
     */
    protected $relationshipClass;

    /**
     * The id attribute of relationship's resource
     * @var string|null
     */
    protected $relationshipIdAttribute;

    /**
     * Create a new relationship from this resource to a relationship resource
     * @param string $attribute        Relationship's attribute in this resource
     * @param string $relationshipResourceType Relationship's resource type
     * @param Relationship::TYPE_TO_ONE|Relationship::TYPE_TO_MANY
     * $relationshipType Relationship type
     * @param string|class|null $relationshipClass [optional]
     * Relationship's class, default is null
     * @param string|null $relationshipIdAttribute [optional]
     * Relationship's id attribute
     */
    public function __construct(
        $attribute,
        $relationshipResourceType,
        $relationshipType = self::TYPE_TO_ONE,
        $relationshipClass = null,
        $relationshipIdAttribute = null
    ) {
        $this->attribute = $attribute;
        $this->relationshipResourceType = $relationshipResourceType;
        $this->relationshipType = $relationshipType;
        $this->relationshipClass = $relationshipClass;
        $this->relationshipIdAttribute = $relationshipIdAttribute;
    }

    /**
     * Get the type of relationship from this resource to relationship resource
     *
     * @return Relationship::TYPE_TO_ONE|Relationship::TYPE_TO_MANY
     */
    public function getRelationshipType()
    {
        return $this->relationshipType;
    }

    /**
     * Get the value of Attribute
     *
     * @return string
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get relationship's resource type
     *
     * @return string
     */
    public function getType()
    {
        return $this->relationshipResourceType;
    }

    /**
     * Get the value of relationshipClass
     *
     * @return string|null
     */
    public function getRelationshipClass()
    {
        return $this->relationshipClass;
    }

    /**
     * Get the id attribute of relationship's resource
     *
     * @return string|null
     */
    public function getRelationshipIdAttribute()
    {
        return $this->$relationshipIdAttribute;
    }
}
