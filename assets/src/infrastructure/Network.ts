/**
 * Very crude polyfill for IE II
 */
export class Network {
    public static get<T>(url: string): Promise<T> {
        return Network.fetch(url, "GET");
    }

    public static post<T>(url: string): Promise<T> {
        return Network.fetch(url, "POST");
    }

    private static fetch<T>(url: string, method: "GET" | "POST" | "PATCH" | "DELETE"): Promise<T> {
        return new Promise<T>(((resolve, reject) => {
            /**
             * Modern browsers: use the nice async Fetch API
             */
            if ("fetch" in window) {
                let request = new Request(url, {method: method});

                fetch(request).then(
                    (response) => {
                        response.json().then(data => {
                            if (200 === response.status) {
                                resolve(<T>data);
                                return;
                            }
                            throw Network.getError(data);
                        }).catch(err => reject(err));
                    }
                ).catch(err => reject(err));

                return;
            }

            /**
             * IE <= 11: time to party like it's 2009
             */
            $.ajax({url: url, method: method}).done(
                data => resolve(data)
            ).fail((jqXhr: JQueryXHR) => {
                reject(Network.getError(jqXhr.responseJSON));
            });
        }));
    }

    private static getError(data: any): Error {
        if (data.hasOwnProperty("errorMessage")) {
            return new Error(data.errorMessage);
        }
        return new Error("There was a server error");
    }
}
