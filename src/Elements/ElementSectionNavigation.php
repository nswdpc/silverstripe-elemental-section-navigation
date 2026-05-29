<?php

namespace Dynamic\Elements\Section\Elements;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use DNADesign\ElementalList\Model\ElementList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\Hierarchy\Hierarchy;
use SilverStripe\Model\List\SS_List;

/**
 * Class ElementSectionNavigation.
 */
class ElementSectionNavigation extends BaseElement
{
    private static string $icon = 'font-icon-menu';

    private static string $singular_name = 'Section Navigation Element';

    private static string $plural_name = 'Section Navigation Elements';

    private static string $table_name = 'ElementSectionNavigation';

    #[\Override]
    public function getPage()
    {
        $area = $this->Parent();

        if ($area instanceof ElementalArea && $area->exists()) {
            if (\class_exists(ElementList::class) && $area->getOwnerPage() instanceof ElementList && $area->getOwnerPage()->exists()) {
                return $area->getOwnerPage()->getPage();
            } else {
                return $area->getOwnerPage();
            }
        }

        return parent::getPage();
    }

    /**
     * Return whether the model provided as the Hierarchy extension applied
     * Hierarchy provides the Children and getParent methods
     */
    protected function hasHierarchy(DataObject $model): bool
    {
        return $model->hasExtension(Hierarchy::class);
    }

    /**
     * Returns children of the provided model, provided it has the Hierarchy extension
     * or a 'Children' method
     */
    protected function getModelChildren(DataObject $model): ?SS_List
    {
        // @phpstan-ignore method.notFound
        return $this->hasHierarchy($model) || $model->hasMethod('Children') ? $model->Children() : null;
    }

    /**
     * Returns parent of the provided model, provided it has the Hierarchy extension
     * or a 'getParent' method
     */
    protected function getModelParent(DataObject $model): ?DataObject
    {
        // @phpstan-ignore method.notFound
        return $this->hasHierarchy($model) || $model->hasMethod('getParent') ? $model->getParent() : null;
    }

    /**
     * Return section navigation of a 'page'. The page may not be a SiteTree object.
     * This preserves BC behaviour of returning parent children (siblings) if the current 'page'
     * has no children
     * This method doesn't take into account ShowInMenus or similar
     */
    public function getSectionNavigation(): ?SS_List
    {
        if (($page = $this->getPage())) {
            if (($children = $this->getModelChildren($page)) && $children->Count() > 0) {
                return $children;
            } elseif ($parent = $this->getModelParent($page)) {
                return $this->getModelChildren($parent);
            } else {
                return null;
            }
        }

        return null;
    }

    #[\Override]
    public function getSummary()
    {
        $page = $this->getPage();
        if ($page) {
            $fragment = _t(self::class  . '.SECTION_NAVIGATION_FOR', 'Section Navigation for {title}', [ 'title' => $page->Title ]);
        } else {
            $fragment = _t(self::class  . '.SECTION_NAVIGATION', 'Section Navigation');
        }

        return DBField::create_field('HTMLFragment', "<p>" .  htmlspecialchars($fragment) . "</p>");
    }

    #[\Override]
    protected function provideBlockSchema()
    {
        $blockSchema = parent::provideBlockSchema();
        $blockSchema['content'] = $this->getSummary();
        return $blockSchema;
    }

    #[\Override]
    public function getType()
    {
        return _t(self::class . '.BLOCK_TYPE', 'Section Navigation');
    }
}
