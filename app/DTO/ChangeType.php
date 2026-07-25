<?php

/**
 * The kind of change a Monitoring scan detected in a resource.
 *
 * @package OxyAI
 */

declare(strict_types=1);

namespace OxyAI\DTO;

/**
 * Per docs/20-Monitoring-Engine.md's "CHANGE DETECTION" list (Created,
 * Modified, Deleted, Broken, Deprecated, Moved, Redirected, Disabled,
 * Expired), narrowed to the three this phase's engines can genuinely
 * detect by diffing successive Discovery Map + generated-content
 * snapshots: a resource appearing, its content/metadata differing from
 * its last known snapshot, or a previously-tracked resource no longer
 * being discovered. Broken/Redirected/Expired need live HTTP-following
 * and SSL-certificate checks; Deprecated/Disabled need a Standard's own
 * lifecycle state this project doesn't model yet; Moved needs location
 * history this phase's snapshot (a single fingerprint per resource id)
 * doesn't retain. See DECISIONS.md.
 */
enum ChangeType: string
{
    case Created = 'created';
    case Modified = 'modified';
    case Deleted = 'deleted';
}
