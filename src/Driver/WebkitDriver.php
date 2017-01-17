<?php

namespace AsyncPHP\Paper\Driver;

use AsyncPHP\Paper\Driver;
use StdClass;

final class WebkitDriver extends BaseDriver implements Driver
{
    /**
     * @var string
     */
    private $binaryPath;

    /**
     * @var string
     */
    private $tempPath;

    /**
     * @var array
     */
    private $options;

    /**
     * @inheritdoc
     */
    public function __construct(string $binaryPath, string $tempPath, array $options = [])
    {
        $this->binaryPath = $binaryPath;
        $this->tempPath = $tempPath;
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

        $hash = md5(spl_object_hash(new StdClass) . $this->html);

        $tempPath = rtrim($this->tempPath, "/");

        $binary = $this->binaryPath;
        $input = "{$tempPath}/{$hash}.html";
        $output = "{$tempPath}/{$hash}.pdf";
        $custom = $this->options;

        return $this->parallel(function() use ($data, $binary, $input, $output, $custom) {
            file_put_contents($input, $data->html);

            $orientation = "Portrait";

            if ($data->orientation === "landscape") {
                $orientation = "Landscape";
            }

            $options = "";

            foreach ($custom as $key => $value) {
                if (is_string($key)) {
                    $options .= " {$key} {$value}";
                } else {
                    $options .= " {$value}";
                }
            }

            exec("
                {$binary} \
                --page-size {$data->size} \
                --orientation {$orientation} \
                --dpi {$data->dpi} \
                --disable-smart-shrinking \
                --load-error-handling 'ignore' \
                --load-media-error-handling 'ignore' \
                {$options} \
                {$input} {$output} \
                > /dev/null 2> /dev/null
            ");

            $contents = file_get_contents($output);

            unlink($input);
            unlink($output);

            return $contents;
        });
    }
}
