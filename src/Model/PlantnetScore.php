<?php

namespace App\Model;

class PlantnetScore
{
    /**
     * Score computed according to users votes (pondered by user reputation)
     * Two decimals
     *
     * @var float
     */
    private $pn;

    /**
     * PN score + partners score
     * Two decimals
     *
     * @var float
     */
    private $total;

    /**
     * Score from Tela Botanica users (one vote = 3 points)
     *
     * @var ?float
     */
    private $tela;

    /**
     * @return float
     */
    public function getPn(): float
    {
        return $this->pn;
    }

    /**
     * @param float $pn
     * @return PlantnetScore
     */
    public function setPn(float $pn): PlantnetScore
    {
        $this->pn = $pn;
        return $this;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     * @return PlantnetScore
     */
    public function setTotal(float $total): PlantnetScore
    {
        $this->total = $total;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getTela(): ?float
    {
        return $this->tela;
    }

    /**
     * @param float|null $tela
     * @return PlantnetScore
     */
    public function setTela(?float $tela): PlantnetScore
    {
        $this->tela = $tela;
        return $this;
    }
}
