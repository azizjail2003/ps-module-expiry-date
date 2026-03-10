<?php
/**
 * 2007-2026 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    abdelaziz jail <jailabdelaziz@icloud.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;

class Ps_Module_Expiry_Date extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'ps_module_expiry_date';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'abdelaziz jail';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Expiry Date');
        $this->description = $this->l('Adds an Expiry Date field to products in BO and displays it in FO.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        return parent::install() &&
            $this->installSql() &&
            $this->registerHook('actionProductFormBuilderModifier') &&
            $this->registerHook('actionAfterCreateProductFormHandler') &&
            $this->registerHook('actionAfterUpdateProductFormHandler') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('actionProductListOverride');
    }

    public function uninstall()
    {
        return parent::uninstall() &&
            $this->uninstallSql();
    }

    /**
     * Add the expiry_date column to the ps_product table.
     */
    protected function installSql()
    {
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . "product` ADD `expiry_date` DATE NULL DEFAULT NULL;";
        try {
            return Db::getInstance()->execute($sql);
        } catch (Exception $e) {
            // Column might already exist
            return true;
        }
    }

    /**
     * Remove the column on uninstall.
     */
    protected function uninstallSql()
    {
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . "product` DROP `expiry_date`;";
        try {
            return Db::getInstance()->execute($sql);
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * Modifies the Product Form in the BO to add the Expiry Date field (PrestaShop 8 Symfony structure)
     */
    public function hookActionProductFormBuilderModifier(array $params)
    {
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];
        
        $formBuilder->add('expiry_date', DateType::class, [
            'label' => $this->l('Expiry Date'),
            'required' => false,
            'widget' => 'single_text',
            'html5' => true,
        ]);
        
        $productId = isset($params['id']) ? (int)$params['id'] : null;

        $expiryDate = null;
        if ($productId) {
            $sql = new DbQuery();
            $sql->select('expiry_date');
            $sql->from('product');
            $sql->where('id_product = ' . (int)$productId);
            $expiryDate = Db::getInstance()->getValue($sql);
        }

        $dateOptions = [
            'label' => $this->l('Expiry Date'),
            'required' => false,
            'widget' => 'single_text',
            'html5' => true,
        ];

        if ($expiryDate) {
            try {
                $dateOptions['data'] = new \DateTime($expiryDate);
            } catch (\Exception $e) {
                // Ignore date parsing errors
            }
        }
        
        $formBuilder->add('expiry_date', DateType::class, $dateOptions);
    }

    /**
     * Save the field when the product is created.
     */
    public function hookActionAfterCreateProductFormHandler(array $params)
    {
        $this->saveExpiryDate($params);
    }

    /**
     * Save the field when the product is updated.
     */
    public function hookActionAfterUpdateProductFormHandler(array $params)
    {
        $this->saveExpiryDate($params);
    }

    /**
     * Common save logic for the date.
     */
    private function saveExpiryDate(array $params)
    {
        $productId = $params['id'];
        /** @var array $formData */
        $formData = $params['form_data'];
        
        $expiryDate = null;
        if (isset($formData['expiry_date']) && $formData['expiry_date'] instanceof \DateTime) {
            $expiryDate = $formData['expiry_date']->format('Y-m-d');
        } elseif (isset($formData['expiry_date']) && is_string($formData['expiry_date'])) {
            $expiryDate = $formData['expiry_date'];
        }

        if ($expiryDate) {
            Db::getInstance()->update(
                'product',
                ['expiry_date' => pSQL($expiryDate)],
                'id_product = ' . (int)$productId
            );
        } else {
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'product` SET `expiry_date` = NULL WHERE `id_product` = ' . (int)$productId
            );
        }
    }

    /**
     * Display the field on the Front Office product page.
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        $productId = isset($params['product']['id_product']) ? $params['product']['id_product'] : null;
        if (!$productId) {
            return '';
        }

        $sql = new DbQuery();
        $sql->select('expiry_date');
        $sql->from('product');
        $sql->where('id_product = ' . (int)$productId);
        $expiryDate = Db::getInstance()->getValue($sql);

        if ($expiryDate) {
            $this->context->smarty->assign([
                'expiry_date' => $expiryDate,
                'is_expired' => (strtotime($expiryDate) < time())
            ]);
            
            return $this->display(__FILE__, 'views/templates/hook/product_expiry.tpl');
        }
        
        return '';
    }

    /**
     * Add column to the product grid in BO
     */
    public function hookActionProductGridDefinitionModifier(array $params)
    {
        /** @var \PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface $definition */
        $definition = $params['definition'];

        $definition->getColumns()->addAfter(
            'reference',
            (new \PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn('expiry_date'))
                ->setName($this->l('Expiry Date'))
                ->setOptions([
                    'field' => 'expiry_date',
                ])
        );
    }

    /**
     * Modify the query to fetch the expiry_date field in BO
     */
    public function hookActionProductGridQueryBuilderModifier(array $params)
    {
        /** @var \Doctrine\DBAL\Query\QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        // The product table is usually aliased as 'p' in the product grid query
        $searchQueryBuilder->addSelect('p.`expiry_date`');
    }
}
