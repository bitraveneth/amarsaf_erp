<?php



namespace App\Support\Assistant;



class AssistantFaqCatalog

{

    public static function entries(): array

    {

        return array_merge(

            AssistantDataCatalog::faqMetricEntries(),

            AssistantDataCatalog::faqStaticEntries()

        );

    }



    public static function quickAsks(): array

    {

        return AssistantDataCatalog::quickAsks();

    }



    public static function findById(string $id): ?array

    {

        foreach (self::entries() as $entry) {

            if ($entry['id'] === $id) {

                return $entry;

            }

        }



        return null;

    }

}

