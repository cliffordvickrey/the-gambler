export class Network {
    public static get<T>(url: string): Promise<T> {
        return Network.fetch(url, "GET");
    }

    public static post<T>(url: string): Promise<T> {
        return Network.fetch(url, "POST");
    }

    private static fetch<T>(url: string, method: "GET" | "POST" | "PATCH" | "DELETE"): Promise<T> {
        return new Promise<T>(((resolve, reject) => {
            let request = new Request(url, {method: method});

            fetch(request).then(
                (response) => {
                    response.json().then(data => {
                        if (200 === response.status) {
                            resolve(<T>data);
                            return;
                        }
                        if (data.hasOwnProperty("errorMessage")) {
                            throw new Error(data.errorMessage);
                        }
                        throw new Error("There was a server error");
                    }).catch(err => reject(err));
                }
            ).catch(err => reject(err));
        }));
    }
}
