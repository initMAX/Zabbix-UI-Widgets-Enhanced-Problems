<div align="center">

<h1>Enhanced Problems</h1>

<p>
developed and maintained by
<a href="https://www.initmax.com"><img alt="initMAX" src="./.readme/logo/initmax-logo-framed.svg" height="22" valign="middle"></a>
and community
</p>

<p><strong>The Zabbix Problems widget, with the filtering and layout an operations wall actually needs.</strong><br>
Zabbix's own Problems widget shows one list, the same way, for everyone looking at it. Enhanced Problems lets a dashboard say what this particular screen is for: which columns, how large, in what colour, and which problems belong on it at all.</p>

<p>
<img src="./.readme/badge/zabbix.svg" alt="Zabbix 6.0-7.4">
<img src="./.readme/badge/version.svg" alt="version">
<img src="./.readme/badge/php.svg" alt="PHP 7.4+">
<img src="./.readme/badge/free.svg" alt="FREE AGPLv3">
<img src="./.readme/badge/gpg.svg" alt="GPG signed">
</p>

<p>
<a href="#what-it-does"><strong>Features</strong></a> &nbsp;·&nbsp;
<a href="#install"><strong>Install</strong></a> &nbsp;·&nbsp;
<a href="#requirements"><strong>Requirements</strong></a> &nbsp;·&nbsp;
<a href="https://portal.initmax.com"><strong>Portal</strong></a> &nbsp;·&nbsp;
<a href="https://www.initmax.com/wiki/enhancedproblems/"><strong>Docs</strong></a>
</p>

<img src="./.readme/screen/01-overview.png" width="880" alt="Enhanced Problems on a Zabbix dashboard">

</div>

---

## Why Enhanced Problems

A problems list is the one widget that ends up on every wall display, every NOC screen and every team's own dashboard - and each of those wants something different from it. One wants the host column wide and everything else gone; another wants severity counts at a glance from ten metres away; a third wants to acknowledge without leaving the page.

Enhanced Problems is that widget with those decisions handed back to whoever builds the dashboard.

## What it does

| Feature                                                          |        |
| ---------------------------------------------------------------- | :----: |
| Choose which columns appear, and how wide each one is            |   Yes  |
| Font size and colour control, for wall displays read at a distance |   Yes  |
| Problem counts by severity                                       |   Yes  |
| Acknowledge a problem from the widget itself                     |   Yes  |
| Filter by host group, host, severity, tags and problem name      |   Yes  |
| One package for Zabbix 6.0 - 7.4                                 |   Yes  |
| Localised into all 25 Zabbix display languages                    |   Yes  |
| High availability ready                                          |   Yes  |

There is no paid edition: everything above is in the one package, under AGPLv3.

## Install

The widget ships as GPG-signed `.deb` and `.rpm` packages.

👉 **Easiest way - the guided installer on the Portal**, which gives you the exact commands for your distribution: <https://portal.initmax.com/catalog/zabbix-enhancedproblems#how-to-install>

Then enable it in **Administration → General → Modules**.

## Requirements

|              |                                                              |
| ------------ | ------------------------------------------------------------ |
| **Zabbix**   | 6.0 · 6.2 · 6.4 · 7.0 · 7.2 · 7.4 - one package covers all   |
| **PHP**      | 7.4 or newer                                                 |
| **OS**       | Debian/Ubuntu · RHEL/Rocky/Alma/Oracle/Amazon · SUSE         |
| **Editions** | FREE (AGPLv3)                                                |
| **Languages** | All 25 Zabbix display languages. The widget ships its own catalogue for every label, section heading, option and help hint it adds, and falls back to Zabbix's own wording for the terms Zabbix already names (Problem, Host, Severity) - so the whole configuration dialog follows each user's language setting |
| **High availability** | Ready. No server-side component and no local state; install it on every frontend node of an HA cluster and any node can serve it |

Zabbix accepts one module format below 6.4 and a different one from 6.4 up, so the package carries both and the frontend loads whichever it understands. What a customer configures, and what the widget draws, is the same on every version in that range: the same fields, under the same names, storing the same values - so a dashboard can move across 6.4 in either direction with nothing lost.

**Upgrading from 7.0-1 or earlier.** Below 6.4 that release stored a configuration of its own, and the two halves of the widget could not read each other's dashboards. Your existing widgets are carried over the first time they are opened - columns keep their order, widths and labels, tags you had listed under "Tag display priority" become tag columns, and your font is preserved. Two settings from the old 6.0/6.2 form have no equivalent and are not approximated: **Show tags**, which showed the first one to three tags of a problem whichever they turned out to be (name the tags you want and the widget gives each one a column), and the **description length limit**, which truncated the problem name (set the Problem column's width instead).

## Support &amp; links

- 📖 **Documentation** - <https://www.initmax.com/wiki/enhancedproblems/>
- 🛒 **Product page** - <https://www.initmax.com/>
- 🔧 **Portal** - <https://portal.initmax.com>
- 💾 **Source code** (AGPLv3) - included in every package and published as a [source archive](https://repo.initmax.com/zabbix/free/zip/enhancedproblems/) on repo.initmax.com
- ✉️ **Support** - <sales@initmax.com>

---

<sub><a href="https://www.gnu.org/licenses/agpl-3.0.html">AGPLv3</a> &nbsp;·&nbsp; © 2021-2026 initMAX s.r.o.</sub>
