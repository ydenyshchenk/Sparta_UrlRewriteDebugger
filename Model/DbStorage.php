<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sparta\UrlRewriteDebugger\Model;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\UrlRewrite\Model\Storage\DbStorage as MagentoDbStorage;

/**
 * @inheritdoc
 */
class DbStorage extends MagentoDbStorage
{
    /**
     * {@inheritdoc}
     */
    public function replace(array $urls)
    {
        if (!$urls) {
            return;
        }

        try {
            $this->doReplace($urls);
        } catch (AlreadyExistsException $e) {
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function insertMultiple($data)
    {
        try {
            $this->connection->insertMultiple($this->resource->getTableName(self::TABLE_NAME), $data);
        } catch (\Exception $e) {
            if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                $message = $e->getMessage();
                $messages = explode(', query was:', $message);

                throw new AlreadyExistsException(
                    __('URL key for specified store already exists: ' . $messages[0])
                );
            }
            throw $e;
        }
    }
}
