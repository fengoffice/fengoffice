<?php

class Currency extends BaseCurrency {

    function getArrayInfo() {

        return array(
            'id' => $this->getId(),
            'symbol' => $this->getSymbol(),
            'name' => $this->getName(),
            'short_name' => $this->getShortName(),
            'is_default' => $this->getIsDefault() ? '1' : '0',
            'external_id' => $this->getExternalId(),
        );
    }

}
