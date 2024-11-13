<?php
/**
 * Bootstrapper Tabbable class
 */

namespace Bootstrapper;

/**
 * Creates Bootstrap 3 compliant tab elements
 *
 * @package Bootstrapper
 */
class Tabbable extends RenderedObject
{
    /**
     * Constant for pill tabs
     */
    const PILL = "pill";
    /**
     * Constant for tab tabs
     */
    const TAB = "tab";
    /**
     * @var Navigation The navigation array
     */
    protected $links;
    /**
     * @var array The contents of the navigation. Should be an array of
     * arrays, with the following inner keys:
     *            <ul>
     *            <li>title - the title of the content</li>
     *            <li>content - the actual content</li>
     *            <li>attributes (optional) - any attributes</li>
     *            </ul>
     */
    protected $contents = [];
    /**
     * @var int Which tab should be open first
     */
    protected $active = 0;
    /**
     * @var string The type
     */
    protected $type = self::TAB;
    /**
     * @var bool Whether we should fade in or not
     */
    protected $fade = false;
    /**
     * @var
     */
    protected $parentId;
    /**
     * Creates a new Tabbable object
     *
     * @param Navigation $links A navigation object
     */
    public function __construct(Navigation $links)
    {
        $this->links = $links->autoroute(false)->withAttributes(
            [
                "role" => "tablist"
            ]
        );
    }
    /**
     * Renders the tabbable object
     *
     * @return string
     */
    public function render()
    {
        $string = $this->renderNavigation();
        $string .= $this->renderContents();

        return $string;
    }
    /**
     * Creates content with a tabbed navigation
     *
     * @param array $contents The content
     * @return $this
     * @see Bootstrapper\Navigation::$contents
     */
    public function tabs($contents = [])
    {
        $this->links->tabs();
        $this->type = self::TAB;

        return $this->withContents($contents);
    }
    /**
     * Creates content with a pill navigation
     *
     * @param array $contents
     * @return $this
     * @see Bootstrapper\Navigation::$contents
     */
    public function pills($contents = [])
    {
        $this->links->pills();
        $this->type = self::PILL;

        return $this->withContents($contents);
    }
    /**
     * Sets the contents
     *
     * @param array $contents An array of arrays
     * @return $this
     * @see Bootstrapper\Navigation::$contents
     */
    public function withContents(array $contents)
    {
        $this->contents = $contents;

        return $this;
    }
    /**
     * Render the navigation links
     *
     * @return string
     */
    protected function renderNavigation()
    {
        $this->links->links($this->createNavigationLinks());

        return $this->links->render();
    }
    /**
     * Creates the navigation links
     *
     * @return array
     */
    protected function createNavigationLinks()
    {
        $links = [];
        $count = 0;

        foreach ($this->contents as $link) {
            $links[] = [
                "link" => '#' . Helpers::slug($link["title"]),
                "title" => $link["title"],
                "linkAttributes" => $this->createLinkAttributes($link),
                "active" => $count == $this->active,
                "parentId" => $this->parentId
            ];

            $count += 1;
        }
        return $links;
    }
    /**
     * @param $link
     * @return array
     */
    protected function createLinkAttributes($link)
    {
        $attributes = [
            "role" => "tab",
            "data-toggle" => $this->type
        ];

        if (array_key_exists("content_id", $link)) {
            $attributes["data-tab-content-id"] = $link["content_id"];
        }

        if (isset($link["data"]) && is_array($link["data"])) {
            if (isset($link["data"]["data-action"]) && $this->parentId) {
                $link["data"]["data-action"] = sprintf(url($link["data"]["data-action"]), $this->parentId);
            }

            $attributes = array_merge($attributes, $link["data"]);
        }

        return $attributes;
    }
    /**
     * Renders the contents
     *
     * @return string
     */
    protected function renderContents()
    {
        $tabs = $this->createContentTabs();
        $string = '<div class=\'tab-content\'>';
        $string .= "<input type='hidden' id='tab-parent_id' value='{$this->parentId}'  >";

        foreach ($tabs as $tab) {
            $string .= "<div {$tab['attributes']}>{$tab['content']}</div>";
        }

        $string .= '</div>';

        return $string;
    }
    /**
     * Creates the content tabs
     *
     * @return array
     */
    protected function createContentTabs()
    {
        $tabs = [];
        $count = 0;

        foreach ($this->contents as $item) {
            $itemAttributes = isset($item["attributes"]) ?
                $item["attributes"] :
                [];

            $attributes = new Attributes(
                $itemAttributes,
                [
                    "class" => "tab-pane",
                    "id" => array_key_exists("content_id", $item) ? $item["content_id"] : Helpers::slug($item["title"])
                ]
            );

            if ($this->fade) {
                $attributes->addClass("fade");
            }

            if ($this->active == $count) {
                $attributes->addClass($this->fade ? "in active" : "active");
            }

            $tabs[] = [
                "content" => $item["content"],
                "attributes" => $attributes
            ];

            $count += 1;
        }

        return $tabs;
    }
    /**
     * Sets which tab should be active
     *
     * @param int $active
     * @return $this
     */
    public function active($active)
    {
        $this->active = $active;

        return $this;
    }
    /**
     * Sets the tabbable objects to fade in
     *
     * @return $this
     */
    public function fade()
    {
        $this->fade = true;

        return $this;
    }
    /**
     * @param $parentId
     * @return $this
     */
    public function parentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }
}
