# Oxy AI Readiness

# Scoring Engine

Version 1.1

---

> **Canonical note (ADR-005):** The GRADE SYSTEM boundaries below are canonical (they already match `docs/28-Testing-Strategy.md`'s grade-boundary tests). The old "AI READINESS LEVELS" section has been replaced with the unified Score/Grade/Label table, which is also the table referenced by `docs/06-Audit-Engine.md`. See `.project/adr/ADR-005-scoring-grading.md`.

---

# Purpose

The Scoring Engine calculates the overall AI Readiness of the website.

Instead of counting passed and failed checks, the engine evaluates weighted importance, relationships between standards, historical improvements and confidence.

The result is a meaningful score that reflects how well a website is prepared for AI systems.

---

# Vision

A score should explain reality.

It should answer:

Can AI discover this website?

Can AI understand it?

Can AI trust it?

Can AI consume it efficiently?

Can AI interact with it?

---

# Responsibilities

Calculate AI Score

Calculate Category Scores

Calculate Trust Score

Calculate Discovery Score

Calculate Infrastructure Score

Calculate Future Readiness

Calculate Confidence

Generate Grade

Generate Recommendations

---

#######################################################

SCORING PIPELINE

#######################################################

Discovery Engine

↓

Validation Engine

↓

Rule Results

↓

Weighting

↓

Category Scores

↓

Confidence

↓

Final Score

↓

Grade

↓

Recommendations

---

#######################################################

CATEGORY SCORES

#######################################################

Discovery

20%

Content

20%

Infrastructure

15%

Headers

10%

Markdown

10%

LLMS

10%

MCP

5%

Agent Skills

5%

Performance

3%

Security

2%

---

#######################################################

SCORE TYPES

#######################################################

Overall Score

Discovery Score

Content Score

Infrastructure Score

Trust Score

Performance Score

Future Readiness

Compliance Score

Accessibility Score

Developer Score

---

#######################################################

GRADE SYSTEM (canonical — ADR-005)

#######################################################

Score | Grade | Label

98-100 | A+ | Excellent

95-97 | A | Excellent

90-94 | A- | Excellent

85-89 | B+ | Advanced

80-84 | B | Advanced

75-79 | B- | Advanced

70-74 | C+ | Good

60-69 | C | Good

40-59 | D | Basic

0-39 | F | Poor

Every score display shows the numeric score plus exactly one Grade and its associated Label. This is the single canonical scale referenced by docs/06-Audit-Engine.md; no other score-band table exists anywhere else in the documentation.

---

#######################################################

WEIGHTING

#######################################################

Critical

Weight 20

High

Weight 10

Medium

Weight 5

Low

Weight 2

Info

Weight 0

---

#######################################################

CONFIDENCE SCORE

#######################################################

Measures confidence in the audit.

Factors

Completed Rules

Skipped Rules

Unavailable Resources

Server Restrictions

Plugin Conflicts

Version Compatibility

Output

Very High

High

Medium

Low

---

#######################################################

BONUS SYSTEM

#######################################################

Award bonus points for

Complete AI Metadata

Valid MCP

Agent Skills

Structured Markdown

Perfect Headers

Excellent Performance

Modern Standards

Zero Critical Issues

---

#######################################################

PENALTIES

#######################################################

Critical Missing Files

Broken Discovery

Invalid Markdown

Broken Robots

Invalid Headers

Security Risks

Duplicate Metadata

Broken MCP

---

#######################################################

TREND ENGINE

#######################################################

Track score history.

Daily

Weekly

Monthly

Quarterly

Yearly

---

#######################################################

TREND STATUS

#######################################################

Improving

Stable

Declining

Unknown

---

#######################################################

INDUSTRY BENCHMARK

#######################################################

Medical

Corporate

Agency

WooCommerce

University

Government

Law Firm

News

Portfolio

Developer

---

#######################################################

COMPARISON

#######################################################

Current

↓

Previous Scan

↓

Previous Month

↓

Previous Release

↓

Historical Average

---

#######################################################

IMPACT ESTIMATION

#######################################################

Every issue includes

Estimated Score Gain

Estimated AI Visibility Gain

Estimated Trust Improvement

Estimated Discovery Improvement

Estimated Fix Time

Estimated Complexity

---

#######################################################

SCORE BREAKDOWN

#######################################################

Discovery

94

Headers

100

Markdown

80

MCP

65

Agent Skills

40

Security

92

Performance

89

Content

91

Infrastructure

96

---

#######################################################

AI READINESS LEVELS (deprecated — superseded by GRADE SYSTEM above, ADR-005)

#######################################################

This section previously defined an independent 6-band label scale that did not agree with the
GRADE SYSTEM boundaries above. It has been removed. Use the Score/Grade/Label table in the
GRADE SYSTEM section for all score display purposes.

---

#######################################################

DASHBOARD

#######################################################

Circular Score

Category Cards

Grade

Trend

Comparison

Recommendations

History

Achievements

---

#######################################################

ACHIEVEMENTS

#######################################################

Perfect Headers

Complete Discovery

AI Ready

Enterprise Ready

Zero Critical Issues

Perfect Markdown

Perfect MCP

Perfect Security

---

#######################################################

REPORT OUTPUT

#######################################################

Overall Score

Grade

Category Scores

Trend

Recommendations

Estimated Improvements

Remaining Issues

Achievements

---

#######################################################

REST API

#######################################################

GET

/score

GET

/score/history

GET

/score/categories

GET

/score/trends

POST

/score/recalculate

---

#######################################################

EVENTS

#######################################################

ScoreCalculated

GradeChanged

TrendUpdated

BenchmarkCompleted

ConfidenceUpdated

---

#######################################################

FILTERS

#######################################################

oxy_ai_score_weights

oxy_ai_score_bonus

oxy_ai_score_penalties

oxy_ai_score_output

---

#######################################################

PERFORMANCE

#######################################################

Calculation

<100ms

Historical Comparison

<200ms

Trend Generation

<500ms

---

#######################################################

SECURITY

#######################################################

Read Only

Capability Validation

Audit Logging

Permission Checks

---

#######################################################

FUTURE FEATURES

#######################################################

Machine Learning Scoring

Competitor Comparison

Industry Percentiles

Predictive Score

AI Visibility Forecast

Cloud Benchmarking

Custom Scoring Profiles

---

# Success Criteria

The score should be stable, explainable and actionable.

Users must understand exactly why they received a score, what improvements matter most and how each fix will affect their AI readiness.

The scoring engine should become the definitive benchmark for AI readiness on WordPress.