// Composable de téléchargement de fichier
export function useFileDownload() {
  function download(url: string, filename?: string) {
    const anchor = document.createElement('a')
    anchor.href = url
    anchor.download = filename ?? ''
    anchor.style.display = 'none'
    document.body.appendChild(anchor)
    anchor.click()
    document.body.removeChild(anchor)
  }

  function downloadFromApi(path: string, filename?: string) {
    download(`/api${path}`, filename)
  }

  return { download, downloadFromApi }
}
