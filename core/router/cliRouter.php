<?php

require_once __DIR__ . '/../../core/J7Router.php';

class cliRouter extends J7Router
{
    public function route()
    {
        $tok = parent::route();

        if( substr($_SERVER['argv'][0],-4)=='.php' )
        {
            $args = array_slice($_SERVER['argv'],2);
        }
        else
        {
            $args = array_slice($_SERVER['argv'],1);
        }

        $p = [];
        $p['param'] = [];
        foreach( $args as $v )
        {
            if( ($s=strpos($v,'='))>0 )
            {
                $left = substr($v,0,$s);
                $right = substr($v,$s+1);

                if( substr($left,-1,1)==']' && strpos($left,'[') )
                {
                    $k = substr($left,0,strpos($left,'['));
                    $v = substr($left,strpos($left,'[')+1,-1);
                    if( !isset($p[$k]) )
                        $p[$k] = [];
                    $p[$k][$v] = $right;
                }
                else
                {
                    $p[ $left ] = $right;
                }
            }
            else
            {
                $p['param'][] = $v;
            }
        }

        $tok->setParams($p);
        return $tok;
    }
}