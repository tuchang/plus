<?php

declare(strict_types=1);

/*
 * +----------------------------------------------------------------------+
 * |                          ThinkSNS Plus                               |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2018 Chengdu ZhiYiChuangXiang Technology Co., Ltd.     |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.0 of the Apache license,    |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available through the world-wide-web at the following url:           |
 * | http://www.apache.org/licenses/LICENSE-2.0.html                      |
 * +----------------------------------------------------------------------+
 * | Author: Slim Kit Group <master@zhiyicx.com>                          |
 * | Homepage: www.thinksns.com                                           |
 * +----------------------------------------------------------------------+
 */

namespace Zhiyi\Plus\FileStorage\Validators;

use function Zhiyi\Plus\setting;
use Zhiyi\Plus\FileStorage\ChannelManager;
use Zhiyi\Plus\FileStorage\Exceptions\NotAllowUploadMimeTypeException;

class CreateTaskValidator extends AbstractValidator
{
    /**
     * Caching configures.
     * @var array
     */
    protected $configure;

    /**
     * Get the validate rules.
     * @return array
     */
    public function rules(bool $image = false): array
    {
        $rules = [
            'filename' => ['bail', 'required', 'string'],
            'hash' => ['bail', 'required', 'string'],
            'size' => ['bail', 'required', 'integer', $this->getAllowMinSize(), $this->getAllowMaxSize()],
            'mime_type' => ['bail', 'required', 'string', $this->getAllowMimeTypes()],
            'storage' => ['bail', 'required', 'array'],
            'storage.channel' => ['bail', 'required', 'string', 'in:'.implode(',', ChannelManager::DRIVES)],
        ];

        if ($image) {
            $rules = array_merge($rules, [
                'dimension' => ['bail', 'required', 'array'],
                'dimension.width' => ['bail', 'required', 'numeric', $this->getAllowImageMinWidth(), $this->getAllowImageMaxWidth()],
                'dimension.height' => ['bail', 'required', 'numeric', $this->getAllowImageMinHeight(), $this->getAllowImageMaxHeight()],
            ]);
        }

        return $rules;
    }

    /**
     * Get image allow min width.
     * @return string
     */
    protected function getAllowImageMinWidth(): string
    {
        return sprintf('min:%d', $this->getConfigure()['image-min-width']);
    }

    /**
     * Get image allow max width.
     * @return string
     */
    protected function getAllowImageMaxWidth(): string
    {
        return sprintf('max:%d', $this->getConfigure()['image-max-width']);
    }

    /**
     * Get image allow min height.
     * @return string
     */
    protected function getAllowImageMinHeight(): string
    {
        return sprintf('min:%d', $this->getConfigure()['image-min-height']);
    }

    /**
     * Get image allow max height.
     * @return string
     */
    protected function getAllowImageMaxHeight(): string
    {
        return sprintf('max:%d', $this->getConfigure()['image-max-height']);
    }

    /**
     * Get allow min file size.
     * @return string
     */
    protected function getAllowMinSize(): string
    {
        return sprintf('min:%d', $this->getConfigure()['file-min-size']);
    }

    /**
     * Get allow max file size.
     * @return string
     */
    protected function getAllowMaxSize(): string
    {
        return sprintf('max:%d', $this->getConfigure()['file-max-size']);
    }

    /**
     * Get allow mime types.
     * @return string
     */
    protected function getAllowMimeTypes(): string
    {
        $mimeTypes = $this->getConfigure()['file-mime-types'];
        if (empty($mimeTypes)) {
            throw new NotAllowUploadMimeTypeException();
        }

        return sprintf('in:%s', implode(',', $mimeTypes));
    }

    protected function getConfigure(): array
    {
        if ($this->configure) {
            return $this->configure;
        }

        return $this->configure = setting('file-storage', 'task-create-validate', [
            'image-min-width' => 0,
            'image-max-width' => 2800,
            'image-min-height' => 0,
            'image-max-height' => 2800,
            'file-min-size' => 2048, // 2KB
            'file-mix-size' => 2097152, // 2MB
            'file-mime-types' => [],
        ]);
    }
}