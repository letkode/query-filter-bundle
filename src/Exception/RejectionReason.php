<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Exception;

/**
 * Machine-readable reason a query parameter was rejected.
 *
 * The string value doubles as the translation key suffix
 * (`query_filter.<value>`) consumers use to render a message.
 */
enum RejectionReason: string
{
    case NotSortable = 'not_sortable';
    case NotFilterable = 'not_filterable';
    case UnknownOperator = 'unknown_operator';
    case MalformedFilter = 'malformed_filter';
}
