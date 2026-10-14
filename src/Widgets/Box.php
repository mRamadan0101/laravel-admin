<?php

namespace Encore\Admin\Widgets;

use Illuminate\Contracts\Support\Renderable;

class Box extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.box';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $content = 'here is the box content.';

    /**
     * @var string
     */
    protected $footer = '';

    /**
     * @var array
     */
    protected $tools = [];

    /**
     * @var string
     */
    protected $script;

    /**
     * @var bool
     */
    protected $solid = false;

    /**
     * Box constructor.
     *
     * @param string $title
     * @param string $content
     */
    public function __construct($title = '', $content = '', $footer = '')
    {
        if ($title) {
            $this->title($title);
        }

        if ($content) {
            $this->content($content);
        }

        if ($footer) {
            $this->footer($footer);
        }

        $this->class('card');
    }

    /**
     * Set box content.
     *
     * @param string|Renderable $content
     *
     * @return $this
     */
    public function content($content)
    {
        if ($content instanceof Renderable) {
            $this->content = $content->render();
        } else {
            $this->content = (string) $content;
        }

        return $this;
    }

    /**
     * Set box footer.
     *
     * @param string|Renderable $footer
     *
     * @return $this
     */
    public function footer($footer)
    {
        if ($footer instanceof Renderable) {
            $this->footer = $footer->render();
        } else {
            $this->footer = (string) $footer;
        }

        return $this;
    }

    /**
     * Set box title.
     *
     * @param string $title
     *
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set box as collapsable.
     *
     * @return $this
     */
    public function collapsable()
    {
        $this->tools[] =
            '<button class="btn btn-tool" data-lte-toggle="card-collapse"><i class="fa-solid fa-minus"></i></button>';

        return $this;
    }

    /**
     *  Set box body scrollable.
     *
     * @param array $options
     *
     * @return $this
     */
    public function scrollable($options = [], $nodeSelector = '')
    {
        $this->id = uniqid('box-slim-scroll-');
        $scrollOptions = json_encode($options);
        $nodeSelector = $nodeSelector ?: '.card-body';

        $this->script = <<<SCRIPT
(function () {
    var options = {$scrollOptions};
    var \$node = $("#{$this->id} {$nodeSelector}").css({'overflow-y': 'auto', 'max-height': options.height || '300px'});
    if (window.OverlayScrollbarsGlobal) {
        OverlayScrollbarsGlobal.OverlayScrollbars(\$node[0], {scrollbars: {autoHide: 'leave'}});
    }
})();
SCRIPT;

        return $this;
    }

    /**
     * Set box as removable.
     *
     * @return $this
     */
    public function removable()
    {
        $this->tools[] =
            '<button class="btn btn-tool" data-lte-toggle="card-remove"><i class="fa-solid fa-xmark"></i></button>';

        return $this;
    }

    /**
     * Set box style.
     *
     * @param string $styles
     *
     * @return $this|Box
     */
    public function style($styles)
    {
        if (is_string($styles)) {
            return $this->style([$styles]);
        }

        $classes = [];

        foreach ($styles as $style) {
            if ($style === 'solid') {
                $this->solid = true;
                continue;
            }

            $classes[] = 'card-'.($style === 'default' ? 'secondary' : $style);
        }

        $this->class = trim($this->class.' '.implode(' ', $classes));

        return $this;
    }

    /**
     * Use a filled header instead of an outlined card.
     *
     * @return $this
     */
    public function solid()
    {
        $this->solid = true;

        return $this;
    }

    /**
     * Variables in view.
     *
     * @return array
     */
    protected function variables()
    {
        return [
            'title'      => $this->title,
            'content'    => $this->content,
            'footer'     => $this->footer,
            'tools'      => $this->tools,
            'attributes' => $this->formatAttributes(),
            'script'     => $this->script,
        ];
    }

    /**
     * Render box.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->solid && preg_match('/\bcard-(primary|secondary|success|info|warning|danger|light|dark)\b/', $this->class)) {
            $this->class .= ' card-outline';
        }

        return view($this->view, $this->variables())->render();
    }
}
