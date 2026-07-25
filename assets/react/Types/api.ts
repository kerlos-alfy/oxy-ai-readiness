/** Shapes mirror the `toArray()` output of their PHP DTO counterparts (see app/DTO/*.php). */

export interface ApiEnvelope<T> {
    success: boolean;
    data?: T;
    message?: string;
}

export type Grade = 'A+' | 'A' | 'A-' | 'B+' | 'B' | 'B-' | 'C+' | 'C' | 'D' | 'F';

export interface ScoreResult {
    score: number;
    grade: Grade;
    label: string;
    confidence: string;
    trend: string;
    calculated_at: string;
}

export interface ValidationResult {
    resource_id: string;
    validator: string;
    status: 'pass' | 'warn' | 'fail';
    message: string;
    execution_time_ms: number;
}

export interface AuditReport {
    scan_type: string;
    summary: Record<string, number>;
    score: ScoreResult;
    duration_ms: number;
    started_at: string;
    results: ValidationResult[];
}

export interface Recommendation {
    id: string;
    title: string;
    description: string;
    category: string;
    priority: string;
    auto_fix_available: boolean;
}

export interface FixResult {
    generator_id: string;
    success: boolean;
    version: number;
    message: string;
    pending: boolean;
}

export interface ModuleStatus {
    published: boolean;
    version: number;
}

export interface ModulePreview {
    content: string;
}

/** The five modules Phase 12 exposes an admin screen for, per docs/03-UI.md's Navigation section, narrowed to what has a REST backend (Phase 8/11). */
export type ModuleSlug = 'robots' | 'llms' | 'markdown' | 'headers' | 'content-signals';
