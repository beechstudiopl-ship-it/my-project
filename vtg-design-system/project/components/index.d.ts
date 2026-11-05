import * as React from 'react';

export type IconName = 'up' | 'down' | 'right' | 'phone' | 'mail' | 'wrench' | 'shield' | 'clock' | 'check' | 'menu';

export interface IconProps { name: IconName; size?: number; label?: string; className?: string; }
export declare function Icon(props: IconProps): React.ReactElement;

export interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  /** primary = blue (main action), amber = report a fault / urgent, secondary = outline, ghost = text link */
  variant?: 'primary' | 'amber' | 'secondary' | 'ghost';
  size?: 'md' | 'lg';
  icon?: IconName;
  /** Renders an <a> when set */
  href?: string;
  children: React.ReactNode;
}
export declare function Button(props: ButtonProps): React.ReactElement;

export interface BadgeProps { tone?: 'neutral' | 'up' | 'down' | 'success' | 'danger'; icon?: IconName; children: React.ReactNode; className?: string; }
export declare function Badge(props: BadgeProps): React.ReactElement;

export interface SectionHeadingProps {
  title: React.ReactNode; lead?: React.ReactNode;
  level?: 1 | 2 | 3; size?: 'h1' | 'h2' | 'display-l'; align?: 'start' | 'center';
  /** Brushed-steel fill — dark ground only */
  steel?: boolean; className?: string;
}
export declare function SectionHeading(props: SectionHeadingProps): React.ReactElement;

export interface FloorIndicatorProps { value: React.ReactNode; label?: string; direction?: 'up' | 'down' | null; ariaLabel?: string; className?: string; }
export declare function FloorIndicator(props: FloorIndicatorProps): React.ReactElement;

export interface GroupSite { num: string; label: string; href?: string; current?: boolean; }
export interface GroupBarProps { sites: GroupSite[]; groupName?: string; className?: string; }
export declare function GroupBar(props: GroupBarProps): React.ReactElement;

export interface SiteMarkProps {
  logoSrc: string; logoHeight?: number;
  /** Omit num/name on the group site (01) — logo only */
  num?: string; name?: string; sub?: string; href?: string; ariaLabel?: string; className?: string;
}
export declare function SiteMark(props: SiteMarkProps): React.ReactElement;

export interface NavLink { label: string; href?: string; active?: boolean; }
export interface NavBarProps {
  brand: React.ReactNode; links: NavLink[]; phone?: string;
  cta?: { label: string; href?: string; variant?: 'amber' | 'primary' }; className?: string;
}
export declare function NavBar(props: NavBarProps): React.ReactElement;

export interface HeroProps {
  title: React.ReactNode; lead?: React.ReactNode; actions?: React.ReactNode;
  image?: string; imageAlt?: string; aside?: React.ReactNode;
  size?: 'display-xl' | 'display-l'; theme?: 'dark' | 'light'; className?: string;
}
export declare function Hero(props: HeroProps): React.ReactElement;

export interface ServiceCardProps {
  num: string; title: string; subtitle?: string; items?: string[]; icon?: IconName;
  href?: string; linkLabel?: string; className?: string;
}
export declare function ServiceCard(props: ServiceCardProps): React.ReactElement;

export interface ProjectCardProps {
  image: string; imageAlt: string; title: string; category?: string; location?: string;
  href?: string; tall?: boolean; className?: string;
}
export declare function ProjectCard(props: ProjectCardProps): React.ReactElement;

export interface FieldProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: string; hint?: string; error?: string; multiline?: boolean; optional?: boolean;
}
export declare function Field(props: FieldProps): React.ReactElement;

export interface FooterColumn { title: string; links: { label: string; href?: string }[]; }
export interface FooterProps { logoSrc?: string; tagline?: string; columns: FooterColumn[]; legal?: React.ReactNode; className?: string; }
export declare function Footer(props: FooterProps): React.ReactElement;
