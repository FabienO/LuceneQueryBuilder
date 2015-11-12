<?php

namespace LuceneQueryBuilder;

/**
 * Lucene Query Builder
 *
 * @author Fabien Oram <fabienoram@gmail.com>
 */

class Query
{
    /**
     * @var array $query to compile
     */
    private $query = array();
    private $appendModifierLink = array();
    public $appendModifier = '';

    /**
     * @param string $phrase Phrase to search
     * @param string $field What field to search?
     *
     * @return $this;
     */
    public function mustContain($phrase, $field = null)
    {
        return $this->add($phrase, $field, 'mustContain');
    }

    /**
     * @param string $phrase Phrase to search
     * @param string $field What field to search?
     *
     * @return $this;
     */
    public function mustNotContain($phrase, $field = null)
    {
        return $this->add($phrase, $field, 'mustNotContain');
    }

    /**
     * @param string $phrase Phrase to search
     * @param string $field What field to search?
     *
     * @return $this;
     */
    public function mayContain($phrase, $field = null)
    {
        return $this->add($phrase, $field, 'mayContain');
    }

    /**
     * @param $phrase
     * @param $phrase2
     * @param null $field
     */
    public function orHas($phrase, $phrase2, $field = null)
    {

    }

    /**
     * @param $phrase
     * @param null $field
     * @param int $distance
     * @return $this
     */
    public function mustHaveInProximity($phrase, $field = null, $distance = 0)
    {
        $this->setAppendModifierLink($phrase, $field, $distance);
        return $this->add($phrase, $field, 'mustHaveInProximity', $distance);
    }

    /**
     * @param $phrase
     * @param null $field
     * @param int $distance
     * @return $this
     */
    public function mayHaveInProximity($phrase, $field = null, $distance = 0)
    {
        $this->setAppendModifierLink($phrase, $field, $distance);
        return $this->add($phrase, $field, 'mayHaveInProximity', $distance);
    }

    /**
     * @param $phrase
     * @param null $field
     * @param int $distance
     */
    private function setAppendModifierLink($phrase, $field = null, $distance = 0)
    {
        $this->appendModifierLink[$field][$phrase] = $distance;
    }

    /**
     * Add a new term
     *
     * @param string $phrase Phrase to search
     * @param string $field What field to search?
     * @param string $modifier How to search that field?
     *
     * @return $this
     */
    public function add($phrase, $field = null, $modifier = null)
    {
        return $this->addPhrase($phrase, $field, $modifier);
    }

    /**
     * @param $phrase
     * @param null $field
     * @param null $modifier
     *
     * @return $this
     */
    private function addPhrase($phrase, $field = null, $modifier = null)
    {
        $this->query[$field][$modifier][] = $phrase;

        return $this;
    }

    /**
     * @param null $modifier
     * @return string
     */
    private function assignModifier($modifier = null)
    {
        if ($modifier)
        {
            switch ($modifier)
            {
                case 'mustContain':
                    return '+';
                case 'mustHaveInProximity':
                    return '+';
                case 'mustNotContain':
                    return '-';
                default:
                    return '';
            }
        }
    }

    /**
     * @param null $modifier
     * @return string
     */
    private function assignAppendModifierCharacter($modifier = null)
    {
        if ($modifier)
        {
            switch ($modifier)
            {
                case 'mustHaveInProximity' || 'mayHaveInProximity':
                    return '~';
                default:
                    '';
            }
        }
    }

    /**
     * @param $modifier
     * @param $field
     * @param $phrase
     * @return string
     */
    private function assignAppendModifier($modifier, $field, $phrase)
    {
        return isset($this->appendModifierLink[$field][$phrase]) ? $this->assignAppendModifierCharacter($modifier) . $this->appendModifierLink[$field][$phrase] : '';
    }

    /**
     * @param array $array The array to create a query from
     *
     * @return $this
     */
    private function Querify($array = array())
    {
        $string = '';
        $y = $x = 1;
        $arraySize = count($array);

        foreach ($array as $field => $arr)
        {
            if ($field !== '')
            {
                if($y > 1)
                {
                    $string .= ' ' . $this->modifierAppend(array_keys($arr)[0]);
                }

                $string .= ' ' . $field . ': ';

                if($x > 1 && $x++ < $arraySize)
                {
                    $string .= ' AND';
                }
            }

            $subArraySize = array_sum(array_map("count", $arr));
            if ($subArraySize > 1 || $field !== '')
            {
                $string .= '(';
            }

            $i = 1;
            foreach ($arr as $modifier => $ar)
            {
                if ($modifier == 'mayContain')
                {
                    $string .= '(';
                }
                
                foreach ($ar as $a)
                {
                    $string .= $this->assignModifier($modifier) . '"' . $a . '"' . $this->assignAppendModifier($modifier, $field, $a);
                    if ($i++ < $subArraySize)
                    {
                        $string .= ' ';
                    }
                }
                
                if ($modifier == 'mayContain')
                {
                    $string .= ')^0.5 ';
                }
            }

            if ($subArraySize > 1 || $field !== '')
            {
                $string .= ')';
            }

            $string .= ' ';
            $y++;
        }

        return trim($string);
    }

    private function modifierAppend($modifier)
    {
        switch($modifier)
        {
            case 'mustContain': return 'AND';
            case 'mustNotContain': return 'NOT';
            default: return '';
        }
    }

    /**
     * Compile all but the start and end characters
     *
     * @return string
     */
    public function compile()
    {
        return $this->Querify($this->query);
    }
}
