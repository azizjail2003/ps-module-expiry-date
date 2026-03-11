# Module PrestaShop 8 - Expiry Date (Date limite de consommation)

Ce dépôt contient le code source du module Prestashop 8.x **`ps_module_expiry_date`**, développé dans le cadre du test technique Nutriweb (Partie 2).

## 🚀 Fonctionnalités
- **Injection BDD** : Ajoute une colonne `expiry_date` de type `DATE` à la table `ps_product` à l'installation.
- **Back Office V2** : Intègre dynamiquement un sélecteur de date (Composant natif `DateType::class` Symfony Forms) au formulaire de la page produit V2 (Symfony).
- **Listing Catalogue** : Modifie la grille principale Doctrine des produits (*Entity Grid Component*) pour afficher la date d'expiration dans une nouvelle colonne personnalisée.
- **Front Office** : S'intègre au `hookDisplayProductAdditionalInfo` pour afficher au client la mention *"Expire le : JJ/MM/AAAA"*. Le module alerte visuellement l'utilisateur si la date est dépassée par rapport au timestamp courant.

## 🛠️ Installation
1. Copiez ou clonez ce dossier directement dans le répertoire `/modules/` de votre boutique PrestaShop 8.
   ```bash
   cd /chemin/vers/prestashop/modules/
   git clone https://github.com/azizjail2003/ps-module-expiry-date.git
   ```
2. Rendez-vous dans le Back Office de PrestaShop, section **Modules > Gestionnaire de modules**.
3. Cherchez "Expiry Date" et cliquez sur **Installer**.

## 💻 Architecture Technique et Hooks utilisés

La compatibilité stricte avec **PrestaShop 8** a été privilégiée en employant les Hooks Symfony V2 plutôt que l'ancienne logique d'onglet (Legacy) :

| Fonction | Hook utilisé | Technologie |
| :--- | :--- | :--- |
| **Création Formulaire V2** | `hookActionProductFormBuilderModifier` | Symfony Forms Component |
| **Persistance des données** | `hookActionProductUpdate` | ObjectModel & Traitement `$POST` |
| **Ajout Colonne UI Grille** | `hookActionProductGridDefinitionModifier` | Composant Grid PrestaShop (PSR) |
| **Query Data Grille** | `hookActionProductGridQueryBuilderModifier` | Doctrine DBAL (QueryBuilder) |
| **Affichage Fiche Produit** | `hookDisplayProductAdditionalInfo` | Moteur de rendu Smarty |

## 👨‍💻 Auteur
**Abdelaziz Jail**
</br>Développement Backend (Laravel / PrestaShop)
</br>Email: jailabdelaziz@icloud.com
