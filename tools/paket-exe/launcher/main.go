// Peluncur Lokerin: membongkar paket aplikasi yang ditempel di akhir file .exe ini,
// menyalakan server PHP bawaan di 127.0.0.1, lalu membuka browser.
package main

import (
	"archive/zip"
	"bufio"
	"fmt"
	"io"
	"net"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"runtime"
	"strings"
	"time"
)

const portAwal = 8765

func main() {
	fmt.Println("==============================================")
	fmt.Println("  Lokerin - lowongan kerja lokal Kec. Pakis")
	fmt.Println("==============================================")
	fmt.Println()

	if err := jalankan(); err != nil {
		fmt.Println()
		fmt.Println("GAGAL:", err)
		tungguEnter()
		os.Exit(1)
	}
}

func jalankan() error {
	exe, err := os.Executable()
	if err != nil {
		return err
	}
	f, err := os.Open(exe)
	if err != nil {
		return err
	}
	defer f.Close()
	st, err := f.Stat()
	if err != nil {
		return err
	}
	paket, err := zip.NewReader(f, st.Size())
	if err != nil {
		return fmt.Errorf("paket aplikasi tidak ditemukan di dalam %s (%v)", filepath.Base(exe), err)
	}

	tujuan := filepath.Join(folderData(), fmt.Sprintf("paket-%d", st.Size()))
	penanda := filepath.Join(tujuan, ".terbongkar")
	if _, err := os.Stat(penanda); err != nil {
		fmt.Println("Menyiapkan Lokerin untuk pertama kali, mohon tunggu...")
		os.RemoveAll(tujuan)
		if err := bongkar(paket, tujuan); err != nil {
			return fmt.Errorf("gagal membongkar paket: %v", err)
		}
		os.WriteFile(penanda, []byte(time.Now().Format(time.RFC3339)), 0o644)
		fmt.Println("Selesai menyiapkan.")
	}

	app := filepath.Join(tujuan, "app")
	php, argsPhp := perintahPhp(tujuan)
	if _, err := os.Stat(php); err != nil {
		return fmt.Errorf("PHP tidak ditemukan di %s", php)
	}

	port := cariPort(portAwal)
	alamat := fmt.Sprintf("127.0.0.1:%d", port)
	url := "http://" + alamat
	router := filepath.Join(app, "vendor", "laravel", "framework", "src", "Illuminate", "Foundation", "resources", "server.php")

	args := append(argsPhp, "-S", alamat, "-t", filepath.Join(app, "public"), router)
	cmd := exec.Command(php, args...)
	cmd.Dir = filepath.Join(app, "public")
	cmd.Env = append(os.Environ(), "APP_URL="+url)
	log, _ := os.Create(filepath.Join(tujuan, "server.log"))
	if log != nil {
		defer log.Close()
		cmd.Stdout, cmd.Stderr = log, log
	}
	if err := cmd.Start(); err != nil {
		return fmt.Errorf("gagal menyalakan PHP: %v", err)
	}
	selesai := make(chan error, 1)
	go func() { selesai <- cmd.Wait() }()

	if !tungguSiap(url, selesai) {
		cmd.Process.Kill()
		return fmt.Errorf("server tidak merespons. Lihat catatan di %s", filepath.Join(tujuan, "server.log"))
	}

	fmt.Println()
	fmt.Println("Lokerin berjalan di:", url)
	fmt.Println("Browser akan terbuka otomatis. Jika tidak, salin alamat di atas ke browser.")
	fmt.Println()
	fmt.Println("Akun demo (kata sandi semua: password)")
	fmt.Println("  Warga/pencari kerja : rizky@lokerin.test")
	fmt.Println("  Pemilik usaha       : usaha6@lokerin.test")
	fmt.Println("  Kader desa          : kader.mangliawan@lokerin.test")
	fmt.Println("  Disnaker            : disnaker@lokerin.test")
	fmt.Println()
	fmt.Println("JANGAN tutup jendela ini selama memakai Lokerin.")
	fmt.Println("Tutup jendela ini untuk menghentikan Lokerin.")
	bukaBrowser(url)

	err = <-selesai
	return fmt.Errorf("server berhenti (%v)", err)
}

func folderData() string {
	if d := os.Getenv("LOCALAPPDATA"); d != "" {
		return filepath.Join(d, "Lokerin")
	}
	if d, err := os.UserCacheDir(); err == nil {
		return filepath.Join(d, "Lokerin")
	}
	return filepath.Join(os.TempDir(), "Lokerin")
}

func bongkar(r *zip.Reader, tujuan string) error {
	akar := filepath.Clean(tujuan) + string(os.PathSeparator)
	total := len(r.File)
	for i, berkas := range r.File {
		nama := strings.ReplaceAll(berkas.Name, "\\", "/")
		jalur := filepath.Join(tujuan, filepath.FromSlash(nama))
		if !strings.HasPrefix(jalur, akar) {
			continue // lindungi dari path di luar folder tujuan
		}
		if strings.HasSuffix(nama, "/") {
			os.MkdirAll(jalur, 0o755)
			continue
		}
		if err := os.MkdirAll(filepath.Dir(jalur), 0o755); err != nil {
			return err
		}
		src, err := berkas.Open()
		if err != nil {
			return err
		}
		dst, err := os.OpenFile(jalur, os.O_CREATE|os.O_WRONLY|os.O_TRUNC, 0o755)
		if err != nil {
			src.Close()
			return err
		}
		_, err = io.Copy(dst, src)
		src.Close()
		dst.Close()
		if err != nil {
			return err
		}
		if i%500 == 0 || i == total-1 {
			fmt.Printf("\r  %d/%d berkas", i+1, total)
		}
	}
	fmt.Println()
	return nil
}

// perintahPhp mengembalikan lokasi php dan opsi agar ekstensi dimuat dari folder paket,
// bukan dari php.ini milik XAMPP di komputer pembuat paket.
func perintahPhp(tujuan string) (string, []string) {
	if p := os.Getenv("LOKERIN_PHP"); p != "" { // untuk pengujian
		return p, nil
	}
	dirPhp := filepath.Join(tujuan, "php")
	nama := "php"
	if runtime.GOOS == "windows" {
		nama = "php.exe"
	}
	args := []string{"-n", "-d", "extension_dir=" + filepath.Join(dirPhp, "ext")}
	for _, e := range []string{"openssl", "mbstring", "fileinfo", "pdo_sqlite", "sqlite3", "curl"} {
		if ada(filepath.Join(dirPhp, "ext", "php_"+e+".dll")) {
			args = append(args, "-d", "extension="+e)
		}
	}
	args = append(args, "-d", "memory_limit=512M", "-d", "variables_order=EGPCS", "-d", "upload_max_filesize=8M")
	return filepath.Join(dirPhp, nama), args
}

func ada(p string) bool { _, err := os.Stat(p); return err == nil }

func cariPort(awal int) int {
	for p := awal; p < awal+50; p++ {
		l, err := net.Listen("tcp", fmt.Sprintf("127.0.0.1:%d", p))
		if err == nil {
			l.Close()
			return p
		}
	}
	return awal
}

func tungguSiap(url string, selesai chan error) bool {
	klien := http.Client{Timeout: 3 * time.Second}
	batas := time.Now().Add(60 * time.Second)
	for time.Now().Before(batas) {
		select {
		case err := <-selesai:
			selesai <- err
			return false
		default:
		}
		if resp, err := klien.Get(url); err == nil {
			resp.Body.Close()
			return true
		}
		time.Sleep(400 * time.Millisecond)
	}
	return false
}

func bukaBrowser(url string) {
	switch runtime.GOOS {
	case "windows":
		exec.Command("rundll32", "url.dll,FileProtocolHandler", url).Start()
	case "darwin":
		exec.Command("open", url).Start()
	default:
		exec.Command("xdg-open", url).Start()
	}
}

func tungguEnter() {
	fmt.Println("Tekan Enter untuk menutup.")
	bufio.NewReader(os.Stdin).ReadString('\n')
}
