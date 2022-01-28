<?php

run();

function run()
{
    $filePath = __DIR__ . '/data.json';
    $rep = new MyRepositoryFile($filePath);
    $widget = new MyWidgetTable($rep);
    $contentTable = $widget->renderContent();

    $layout = __DIR__ . '/layout.php';
    $contentFull = MyView::renderPhp($layout, ['content' => $contentTable]);

    file_put_contents(__DIR__ . '/result.html', $contentFull);
}

class MyWidgetTable
{
    public $data;
    public $fields;
    private $fieldsRequired = [
        'id',
        'email',
        'phone',
        'text',
    ];

    function __construct(MyRepository $repository, array $fields = [])
    {
        $this->data = $repository->getData();

        if (empty($this->data)) {
            throw new \Exception('data is required');
        }

        if (empty($fields)) {
            $this->fields = array_keys(current($this->data));
        } else {
            $this->fields = $fields;
        }

        $this->cleanData();
    }

    /**
     * @return void
     */
    private function cleanData()
    {
        foreach ($this->data as $idx => $item) {
            if ($this->hasToRemoveItem($item)) {
                unset($this->data[$idx]);
            }
        }
    }

    /**
     * @param array $item
     * @return bool
     */
    private function hasToRemoveItem(array &$item): bool
    {
        foreach ($this->fieldsRequired as $field) {
            if (empty($item[$field])) {
                return true;
            }
        }

        return false;
    }

    public function renderContent()
    {
        $view = '/app/runtime/files/widget.php';
        return MyView::renderPhp($view, ['widget' => $this]);
    }
}

class MyView
{
    public static function renderPhp(string $path, array $args = [])
    {
        ob_start();
        extract($args, EXTR_OVERWRITE);
        include($path);
        $var=ob_get_contents();
        ob_end_clean();
        return $var;
    }
}

abstract class MyRepository
{
    abstract public function getData();
}

class MyRepositoryFile extends MyRepository
{
    public $filePath;

    function __construct(string $path)
    {
        if (empty($path)) {
            throw new \Exception('path is required');
        }

        $this->filePath = $path;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        $data = file_get_contents($this->filePath);
        return json_decode($data, true);
    }
}
