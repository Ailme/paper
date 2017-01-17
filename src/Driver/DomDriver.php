<?php

namespace AsyncPHP\Paper\Driver;

use AsyncPHP\Paper\Driver;
use Dompdf\Dompdf;
use Dompdf\Options;

final class DomDriver extends BaseDriver implements Driver
{
    /**
     * @var array
     */
    private $options;

    /**
     * @inheritdoc
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     *
     * @return Promise
     */
    public function render()
    {
        $data = $this->data();
        $custom = $this->options;

        return $this->parallel(function() use ($data, $custom) {
            $options = new Options();
            $options->set("isJavascriptEnabled", true);
            $options->set("isPhpEnabled", false);
            $options->set("isHtml5ParserEnabled", true);
            $options->set("dpi", $data->dpi);

            foreach ($custom as $key => $value) {
                $options->set($Key, $value);
            }

            $engine = new Dompdf($options);
            $engine->setPaper($data->size, $data->orientation);
            $engine->loadHtml($data->html);
            $engine->render();

            return $engine->output();
        });
    }
}
