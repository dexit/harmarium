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
