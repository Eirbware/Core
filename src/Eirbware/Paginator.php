<?php

namespace Eirbware;

/**
 * Gère la pagination de résultat issus
 * d'une requête par Query Builder
 */
class Paginator implements \IteratorAggregate
{
    /**
     * Configuration
     * $urlKey : clef  utilisé dans l'url 
     * pour référencer la page
     */
    public static $urlKey = 'p';

    /**
     * QueryBuilder à utiliser
     */
    private $queryBuilder;

    /**
     * Page courrante
     */
    private $page;

    /**
     * Nombre de pages
     */
    private $pages_nb;

    /*
     * Nombre de lignes
     */
    private $lines_nb;

    /**
     * Interval des pages affichée avant et après
     * la page courante
     */
    private $pages_interval;

    /**
     * Résultats par page
     */
    private $results_per_page;

    public function __construct(
            $queryBuilder, 
            $results_per_page = 30, 
            $pages_interval = 5,
            $page = 1
        )
    {
        $this->queryBuilder = $queryBuilder;
        $this->results_per_page = $results_per_page;
        $this->page = $page;
        $this->pages_interval = $pages_interval;
        $this->calculatePagesNumber();
        $this->setPageByURL();
    }

    /**
     * Affecte la page courante
     */
    public function setPage($page)
    {
        if ($page >= 1 && $page <= $this->pages_nb) {
            $this->page = $page;
        }
    }

    /**
     * Affecte la page courante à partir
     * de l'url
     */
    public function setPageByURL()
    {
        if (isset($_GET[self::$urlKey])) {
            $this->page = $_GET[self::$urlKey];
        }
    }

    /**
     * Retourne le code html du browser de page
     */
    public function writeBrowser()
    {
        $url = $_SERVER['REQUEST_URI'];
        if (!strpos($url, '?')) {
            $url .= '?';
        }
        $url = preg_replace('#&'.self::$urlKey.'=[0-9]#', '', $url);
        $url = preg_replace('#\?'.self::$urlKey.'=[0-9]#', '?', $url);

        $html = null;
        $html .= '<a href="'.$url.'&'.self::$urlKey.'='.$this->firstNumber().'">&laquo;</a>';
        foreach ($this as $page) {
            if ($page == $this->page) {
                $html .= '<strong>'.$page.'</strong>';
            } else {
                $html .= '<a href="'.$url.'&'.self::$urlKey.'='.$page.'">';
                $html .= $page;
                $html .= '</a>';
            }
        }
        $html .= '<a href="'.$url.'&'.self::$urlKey.'='.$this->lastNumber().'">&raquo;</a>';

        return $html;
    }

    /**
     * Calcule le nombre de pages
     * et enregistre le nombre de ligne
     */
    private function calculatePagesNumber()
    {
        $qb = clone $this->queryBuilder;
        
        $sql = $qb->getSQL();
        $sqlParts = array_keys($qb->getQueryParts());
        $qb->resetQueryParts($sqlParts);
        $qb->select('COUNT(*) as nb')->from('('.$sql.')', 'T');

        $datas = $qb->execute()->fetch(PDO::FETCH_ASSOC);
        $this->lines_nb = $datas['nb'];

        $this->pages_nb = ceil($this->lines_nb/$this->results_per_page);
    }

    /**
     * Obtenir le numéro de la page courrante
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Obternir le nombre de pages
     */
    public function getPagesNb()
    {
        return $this->pages_nb;
    }

    /**
     * Obtenir le nombre total de ligne
     */
    public function getLinesNb()
    {
        return $this->lines_nb;
    }

    /**
     * Test si la requete est vide
     */
    public function isEmpty()
    {
        return ($this->lines_nb == 0);
    }

    /**
     * Obtenir les données paginée
     */
    public function getDatas()
    {
        $qb = clone $this->queryBuilder;

        return $qb
            ->setFirstResult(($this->page-1) * $this->results_per_page)
            ->setMaxResults($this->results_per_page)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Est-ce la premièrep page ?
     */
    public function isFirst()
    {
        return $this->page == $this->firstNumber();
    }

    /**
     * Est-ce la dernière page ?
     */
    public function isLast()
    {
        return $this->page == $this->lastNumber();
    }

    /**
     * Numéro de la première page
     */
    public function firstNumber()
    {
        return 1;
    }

    /**
     * Numéro de la dernière page
     */
    public function lastNumber()
    {
        return max(1, $this->pages_nb);
    }

    /**
     * Obtenir un iterateur sur les pages à mettre dans le navigateur
     */
    public function getIterator()
    {
        $min = max($this->firstNumber(), $this->page-$this->pages_interval);
        $max = min($this->lastNumber(), $this->page+$this->pages_interval);
        
        $pages = range($min, $max);

        return new \ArrayIterator($pages);
    }
}
