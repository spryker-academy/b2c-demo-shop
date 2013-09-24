<?php
namespace Pyz\Zed\Catalog\Component\Internal;

/**
 * Class Install
 * @package Pyz\Zed\Catalog\Component\Internal
 * @property \Generated_Zed_Catalog_Component_Factory $factory
 */
class Install extends \ProjectA_Zed_Catalog_Component_Internal_Install
{
    /**
     * @var string
     */
    protected $attributeConstantInterface = 'Pyz\Shared\Catalog\Code\ProductAttributeConstant';

    /**
     * @var string
     */
    protected $optionTypeConstantInterface = 'Pyz\Shared\Catalog\Code\ProductOptionTypeConstant';

    /**
     * @var string
     */
    protected $attributeSetConstantInterface = 'Pyz\Shared\Catalog\Code\ProductAttributeSetConstant';

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     *
     */
    public function install()
    {
        //do the parental stuff
        parent::install();

        //attributeSets
        $this->installAttributeSets();

        //valueTypes to attribute mapping foreach attributeSet
        $this->createValueTypesForAttributeSets();

        //create option values, needed to add Single- or MultiOptions
        $this->addOptionValuesForAttributes();

        //attribute to group mapping
        $this->addAttributesToAttributeGroups();

        //valueTypes to attributeSetGroups mapping
        $this->addAttributeValueTypesToAttributeSetGroups();
    }

    /**
     * add attribute sets from constant interface
     */
    public function installAttributeSets()
    {
        $constantInterface = new \ReflectionClass($this->attributeSetConstantInterface);
        foreach ($constantInterface->getConstants() as $name) {
            $query = new \ProjectA_Zed_Catalog_Persistence_PacCatalogAttributeSetQuery();
            $query->filterByName($name);
            $query->findOneOrCreate()->save();
        }
    }

    /**
     * create the valueTypes foreach attributeSet defined in Tir_Catalog_Internal_AttributeToVarietyMapping
     * @see Nu3_Catalog_Internal_AttributeToVarietyMapping
     */
    protected function createValueTypesForAttributeSets()
    {
        $attributes = $this->factory->getModelFinder()->getAttributesMap();
        $attributeSets = $this->factory->getModelFinder()->getAttributeSetsMap();
        foreach(AttributeToVarietyMapping::$attributesToVarietyMapping as $attributeSetName => $attributesToVarietyMapping) {
            /* @var $attributeSet \ProjectA_Zed_Catalog_Persistence_PacCatalogAttributeSet */
            $attributeSet = $attributeSets->get($attributeSetName);
            if (is_array($attributesToVarietyMapping) && !empty($attributesToVarietyMapping)) {
                foreach ($attributesToVarietyMapping as $attributeName => $variety) {
                    /* @var $attribute \ProjectA_Zed_Catalog_Persistence_PacCatalogAttribute */
                    $attribute = $attributes->get($attributeName);
                    // check if the value variety of that attribute for the specific attributeSet is already set
                    $attributeValueTypeQuery = new \ProjectA_Zed_Catalog_Persistence_PacCatalogValueTypeQuery();
                    /* @var $valueType \ProjectA_Zed_Catalog_Persistence_PacCatalogValueType */
                    $valueType =
                        $attributeValueTypeQuery
                            ->filterByAttributeSet($attributeSet)
                            ->filterByAttribute($attribute)
                            ->findOne();

                    if (!$valueType) {
                        // now create the value type entry
                        $entityValueType = new \ProjectA_Zed_Catalog_Persistence_PacCatalogValueType();
                        $entityValueType->setAttributeSet($attributeSet);
                        $entityValueType->setAttribute($attribute);
                        $entityValueType->setVariety($variety);
                        $entityValueType->save();
                    } else {
                        $valueType->setVariety($variety);
                        $valueType->save();
                    }
                }
            }
        }
    }

    /**
     * range attribute into defined groups
     * @see Nu3_Catalog_Internal_AttributeToGroupMapping
     */
    protected function addAttributesToAttributeGroups()
    {
        $groups = $this->factory->getModelFinder()->getGroupsMap();
        $attributes = $this->factory->getModelFinder()->getAttributesMap();

        foreach (AttributeToAttributeGroupMapping::$attributesToGroupMapping as $attributeName => $groupNames) {
            /* @var $attribute \ProjectA_Zed_Catalog_Persistence_PacCatalogAttribute */
            $attribute = $attributes->get($attributeName);
            $attribute->getGroups();
            foreach ($groupNames as $groupName) {
                $group = $groups->get($groupName);
                $attribute->addGroup($group);
            }
            $attribute->save();
        }
    }

    /**
     * range attribute into defined groups
     * @see Nu3_Catalog_Internal_AttributeToGroupMapping
     */
    protected function addAttributeValueTypesToAttributeSetGroups()
    {
        $groups = $this->factory->getModelFinder()->getGroupsMap();
        $attributes = $this->factory->getModelFinder()->getAttributesMap();
        $attributeSets = $this->factory->getModelFinder()->getAttributeSetsMap();

        foreach (AttributeValueTypeToAttributeSetGroupMapping::getMappings() as
                 $attributeSetName => $valueTypeToAttributeSetGroupMapping) {
            if (!empty($valueTypeToAttributeSetGroupMapping)) {
                foreach ($valueTypeToAttributeSetGroupMapping as $attributeName => $groupNames) {

                    //first we need to catch the valueType
                    $query = new \ProjectA_Zed_Catalog_Persistence_PacCatalogValueTypeQuery();
                    $query->filterByAttribute($attributes->get($attributeName));
                    $query->filterByAttributeSet($attributeSets->get($attributeSetName));
                    $valueTypeEntity = $query->findOne();

                    //now we can add the attributeSetGroups to the valueType
                    foreach ($groupNames as $group) {
                        $query = new \ProjectA_Zed_Catalog_Persistence_PacCatalogAttributeSetGroupQuery();
                        $query->filterByGroup($groups->get($group));
                        $query->filterByValueType($valueTypeEntity);
                        $query->findOneOrCreate()->save();
                    }
                }
            }
        }
    } //end addAttributeValueTypesToAttributeSetGroups

    /**
     * create option values, needed to add Single- or MultiOptions
     */
    protected function addOptionValuesForAttributes()
    {
        /* @var $attributeSet \ProjectA_Zed_Catalog_Persistence_PacCatalogAttributeSet */
        foreach ($this->factory->getModelFinder()->getAttributeSetsMap() as $attributeSet) {
            $attributeOptionsMap = $this->factory->getInternalImportOptionsForAttributes()->getOptionsForAttributeMap($attributeSet->getName());
            foreach ($attributeOptionsMap as $attributeName => $attributeOptions) {
                $query = new \ProjectA_Zed_Catalog_Persistence_PacCatalogValueTypeQuery();
                /* @var $entityValueType \ProjectA_Zed_Catalog_Persistence_PacCatalogValueType */
                $entityValueType =
                    $query
                        ->filterByAttributeSet($attributeSet)
                        ->useAttributeQuery()->filterByName($attributeName)->endUse()
                        ->findOne();
                foreach ($attributeOptions as $option) {
                    $valueOptionQuery = new \ProjectA_Zed_Catalog_Persistence_PacCatalogValueOptionQuery();
                    $valueOptionQuery
                        ->filterByValueType($entityValueType)
                        ->filterByName($option)
                        ->findOneOrCreate()
                        ->save();
                }
            }
        }
    }
}
