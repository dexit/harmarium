const API_URL = process.env.WORDPRESS_API_URL;

export async function fetchAPI(endpoint: string, options: RequestInit = {}) {
  const headers = { 'Content-Type': 'application/json' };

  if (!API_URL) {
    throw new Error('WORDPRESS_API_URL is not defined');
  }

  const res = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers: {
      ...headers,
      ...options.headers,
    },
    next: { revalidate: 60 },
  });

  if (!res.ok) {
    console.error(await res.text());
    throw new Error('Failed to fetch API');
  }

  const json = await res.json();
  return json;
}

export async function getPosts() {
  return fetchAPI('/wp/v2/posts?_embed');
}

export async function getPostBySlug(slug: string) {
  const posts = await fetchAPI(`/wp/v2/posts?slug=${slug}&_embed`);
  return posts[0];
}

export async function getMedia() {
  return fetchAPI('/wp/v2/media?per_page=20');
}

// ── Portfolio CPT ────────────────────────────────────────────────────────────

export async function getPortfolioItems(perPage = 20): Promise<WPPortfolioItem[]> {
  return fetchAPI(
    `/wp/v2/portfolio?per_page=${perPage}&_embed&_fields=id,title,link,excerpt,_embedded,harmarium`
  );
}

export async function getPortfolioItemBySlug(slug: string): Promise<WPPortfolioItem | undefined> {
  const items = await fetchAPI(`/wp/v2/portfolio?slug=${slug}&_embed`);
  return items[0];
}

// ── WooCommerce Products (via WP REST API) ───────────────────────────────────

export async function getProducts(perPage = 20): Promise<WPProduct[]> {
  return fetchAPI(
    `/wc/v3/products?per_page=${perPage}&status=publish`
  );
}

export async function getProductBySlug(slug: string): Promise<WPProduct | undefined> {
  const products = await fetchAPI(`/wc/v3/products?slug=${slug}`);
  return products[0];
}

// ── Harmarium custom REST endpoints ─────────────────────────────────────────

export async function submitCommission(data: CommissionPayload): Promise<CommissionResult> {
  if (!API_URL) throw new Error('WORDPRESS_API_URL is not defined');

  const res = await fetch(`${API_URL}/harmarium/v1/commission`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data),
  });

  const json = await res.json();
  if (!res.ok) {
    throw new Error(json?.message ?? 'Failed to submit commission request');
  }
  return json as CommissionResult;
}

export async function getQRCode(url: string, size = 4): Promise<{ svg: string; data_uri: string }> {
  return fetchAPI(`/harmarium/v1/qr?url=${encodeURIComponent(url)}&size=${size}`);
}

// ── Types ────────────────────────────────────────────────────────────────────

export type WPMedia = {
  id: number;
  source_url: string;
  title: { rendered: string };
  alt_text: string;
  media_details: {
    width: number;
    height: number;
  };
};

export type HarmariumMeta = {
  hm_medium?: string;
  hm_dimensions?: string;
  hm_year?: string;
  hm_availability?: 'available' | 'sold' | 'reserved' | 'nfs';
  hm_price_display?: string;
  hm_edition?: string;
  hm_certificate_nr?: string;
};

export type WPPortfolioItem = {
  id: number;
  title: { rendered: string };
  excerpt: { rendered: string };
  link: string;
  harmarium?: HarmariumMeta;
  _embedded?: {
    'wp:featuredmedia'?: Array<WPMedia>;
  };
};

export type WPProductImage = {
  id: number;
  src: string;
  alt: string;
};

export type WPProduct = {
  id: number;
  name: string;
  slug: string;
  permalink: string;
  price: string;
  regular_price: string;
  sale_price: string;
  description: string;
  short_description: string;
  images: WPProductImage[];
  stock_status: 'instock' | 'outofstock' | 'onbackorder';
  categories: Array<{ id: number; name: string; slug: string }>;
};

export type CommissionPayload = {
  name: string;
  email: string;
  subject: string;
  message: string;
  budget?: string;
  timeline?: string;
  nonce: string;
};

export type CommissionResult = {
  id: number;
  message: string;
};
