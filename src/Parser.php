<?php
/**
 * Created by PhpStorm.
 * User: avrban
 * Date: 09.03.2016
 * Time: 15:44
 */

namespace Buxus\Bql;

use Buxus\Bql;

class Parser
{
    public static function parseBQL($bqlQuery){
        //echo $query;
        $bqlQuery=strtolower($bqlQuery);
        $bqlQuery=str_replace("group by","groupby",$bqlQuery);
        $bqlQuery=str_replace("order by","orderby",$bqlQuery);

        $queryParts = explode(" ", $bqlQuery);


        /*$dqlQuery=new Query();
        $dqlQuery->setDQL($query);
        $dqlParser=new Query\Parser($dqlQuery);
        echo $dqlParser->getAST()->selectClause;
        */

        $query=new Query();
        $currentClause="";

        foreach($queryParts as $queryPart) {
            if($queryPart=="select" && empty($query->selectClause)) { $currentClause=&$query->selectClause; continue; }
            else if($queryPart=="from" && empty($query->fromClause)) { $currentClause=&$query->fromClause; continue; }
            else if($queryPart=="where" && empty($query->whereClause)) { $currentClause=&$query->whereClause; continue; }
            else if($queryPart=="groupby" && empty($query->groupByClause)) { $currentClause=&$query->groupByClause; continue; }
            else if($queryPart=="having" && empty($query->havingClause)) { $currentClause=&$query->havingClause; continue; }
            else if($queryPart=="orderby" && empty($query->orderByClause)) { $currentClause=&$query->orderByClause; continue; }
            else if($queryPart=="limit" && empty($query->limitClause)) { $currentClause=&$query->limitClause; continue; }

            $currentClause.=$queryPart." ";
        }

        echo "
        *********************
        SELECT: ".$query->selectClause."
        FROM: ".$query->fromClause."
        WHERE: ".$query->whereClause."
        HAVING: ".$query->havingClause."
        GROUP BY: ".$query->groupByClause."
        ORDER BY: ".$query->orderByClause."
        *********************\n";



        return $query;
    }
}
?>