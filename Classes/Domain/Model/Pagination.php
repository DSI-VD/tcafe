<?php
namespace Vd\Tcafe\Domain\Model;

use TYPO3\CMS\Core\Database\Query\QueryBuilder;

class Pagination
{
    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @var array
     */
    protected $config;

    /**
     * @var int
     */
    private $numberOfPages;

    /**
     * @var int
     */
    private $displayRangeStart;

    /**
     * @var int
     */
    private $displayRangeEnd;

    /**
     * @var bool
     */
    private $hasLessPages;

    /**
     * @var bool
     */
    private $hasMorePages;

    /**
     * @var int
     */
    private $lastPage;

    /**
     * @var int
     */
    private $nextPage;

    /**
     * @var int
     */
    private $previousPage;

    /**
     * @param array|null $config
     * @param string $table
     * @param int $currentPage
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(?array $config, string $table, int $currentPage, QueryBuilder $queryBuilder)
    {
        $this->config = $config;
        if ($this->config !== null) {
            $queryBuilder->resetQueryParts(['select', 'limit', 'from']);
            $queryBuilder->getConcreteQueryBuilder()->setFirstResult(null);
            $queryBuilder->getConcreteQueryBuilder()->setMaxResults(null);
            $recordsCount = $queryBuilder
                ->count('uid')
                ->from($table)
                ->execute()
                ->fetchColumn(0);

            $this->numberOfPages = 0;
            $itemsPerPage = $this->config['itemsPerPage'];
            if ($itemsPerPage !== 0) {
                $this->numberOfPages = ceil($recordsCount / $itemsPerPage);
            }

            // Data needed to build the pagination
            $maximumNumberOfLinks = $this->config['maximumNumberOfLinks'];
            if ($maximumNumberOfLinks > $this->numberOfPages) {
                $maximumNumberOfLinks = $this->numberOfPages;
            }

            $delta = floor($maximumNumberOfLinks / 2);
            $displayRangeStart = $currentPage - $delta;
            $displayRangeEnd = $currentPage + $delta - ($maximumNumberOfLinks % 2 === 0 ? 1 : 0);

            if ($displayRangeStart < 1) {
                $displayRangeEnd -= $displayRangeStart - 1;
            }

            if ($displayRangeEnd > $this->numberOfPages) {
                $displayRangeStart -= $displayRangeEnd - $this->numberOfPages;
            }

            $displayRangeStart = (int)max($displayRangeStart, 1);
            $displayRangeEnd = (int)min($displayRangeEnd, $this->numberOfPages);

            $this->displayRangeStart = $displayRangeStart - 1;
            $this->displayRangeEnd = $displayRangeEnd + 1;
            $this->hasLessPages = $displayRangeStart > 2;
            $this->hasMorePages = $displayRangeEnd + 1 < $this->numberOfPages;
            $this->lastPage = $this->numberOfPages - 1;
            $this->nextPage = $currentPage + 1;
            $this->previousPage = $currentPage - 1;
            $this->pages = [];
            for ($i = 0; $i < $this->numberOfPages; $i++) {
                $this->pages[] = [
                    'active' => $i == $currentPage,
                    'label' => $i + 1,
                    'index' => $i
                ];
            }
        }
    }

    /**
     * @return array
     */
    public function getPages(): array
    {
        return $this->pages;
    }

    /**
     * @return int
     */
    public function getNumberOfPages(): int
    {
        return $this->numberOfPages;
    }

    /**
     * @return int
     */
    public function getDisplayRangeStart(): int
    {
        return $this->displayRangeStart;
    }

    /**
     * @return int
     */
    public function getDisplayRangeEnd(): int
    {
        return $this->displayRangeEnd;
    }

    /**
     * @return bool
     */
    public function isHasLessPages(): bool
    {
        return $this->hasLessPages;
    }

    /**
     * @return bool
     */
    public function hasLessPages(): bool
    {
        return $this->hasLessPages;
    }

    /**
     * @return bool
     */
    public function isHasMorePages(): bool
    {
        return $this->hasMorePages;
    }

    /**
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->hasMorePages;
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * @return int
     */
    public function getNextPage(): int
    {
        return $this->nextPage;
    }

    /**
     * @return int
     */
    public function getPreviousPage(): int
    {
        return $this->previousPage;
    }
}
